<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Contract;
use App\Models\Stage;
use App\Models\Checklist;
use App\Models\Checkitem;
use App\Models\Log;
use Tests\TestCase;

class ContractLifecycleTest extends TestCase
{
    protected User $user;
    protected Company $company;
    protected Contact $contact;
    protected Product $product;
    protected Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::create([
            'name' => 'Test Operator',
            'username' => 'operator',
            'password' => bcrypt('password123'),
            'role' => 'ROLE_ADMIN',
            'active' => true,
        ]);

        $this->company = Company::firstOrCreate(
            ['code' => 'MENON'],
            ['name' => 'Menon Trading LLC', 'active' => true]
        );

        $this->contact = Contact::firstOrCreate(
            ['name' => 'John Seller'],
            ['companyId' => $this->company->id, 'active' => true]
        );

        $this->product = Product::firstOrCreate(
            ['code' => 'FERROUS'],
            ['name' => 'Ferrous Scrap Metal', 'productCategory' => 'FERROUS', 'active' => true]
        );

        $this->currency = Currency::firstOrCreate(
            ['description' => 'USD'],
            ['symbol' => '$', 'rate' => 1.0]
        );
    }

    public function test_can_create_buy_contract_with_automatic_order_code()
    {
        $response = $this->actingAs($this->user)->post('/contracts', [
            'type' => 'BUY',
            'meta_company_id' => $this->company->id,
            'contact_id' => $this->contact->id,
            'product_id' => $this->product->id,
            'quantity' => 150.5,
            'unit_price' => 2450.00,
            'currency_id' => $this->currency->id,
            'delivery_terms' => 'CIF',
            'mode_of_payment' => 'TT in Advance',
        ]);

        $response->assertRedirect();
        
        $contract = Contract::whereHas('purchase', function($q) {
            $q->where('meta_company_id', $this->company->id);
        })->latest('id')->first();

        $this->assertNotNull($contract);
        $this->assertEquals('BUY', $contract->type);
        $this->assertEquals('ACTIVE', $contract->contractStatus);
        
        // Assert Order Code matches pattern {COMPANY}{CATEGORY}{YY}-{SEQ}
        $expectedPrefix = 'MENONFERROUS' . date('y');
        $this->assertStringStartsWith($expectedPrefix, $contract->orderCode);

        // Assert Audit Log was created
        $log = Log::where('module', 'CONTRACT')
            ->where('dataId', $contract->id)
            ->where('action', 'CREATE')
            ->first();
        $this->assertNotNull($log);
    }

    public function test_can_advance_contract_to_next_stage()
    {
        $contract = Contract::where('active', true)->first();
        $this->assertNotNull($contract);

        $stages = Stage::orderBy('priority')->get();
        if ($stages->count() < 2) {
            $stage1 = Stage::create(['name' => 'Draft', 'priority' => 1, 'model_id' => 1]);
            $stage2 = Stage::create(['name' => 'Under Review', 'priority' => 2, 'model_id' => 1]);
            $targetStage = $stage2;
        } else {
            $targetStage = $stages->where('id', '!=', $contract->stage_id)->first();
        }

        $response = $this->actingAs($this->user)
            ->from('/contracts')
            ->post("/contracts/{$contract->id}/move", [
                'stage_id' => $targetStage->id,
            ]);

        $response->assertRedirect('/contracts');
        $contract->refresh();
        $this->assertEquals($targetStage->id, $contract->stage_id);
    }

    public function test_purchase_contract_cannot_be_closed_without_all_sales_associated()
    {
        // A newly created BUY contract without sale product association must reject closing
        $response = $this->actingAs($this->user)->post('/contracts', [
            'type' => 'BUY',
            'meta_company_id' => $this->company->id,
            'contact_id' => $this->contact->id,
            'product_id' => $this->product->id,
            'quantity' => 50.0,
            'unit_price' => 1200.00,
            'currency_id' => $this->currency->id,
        ]);

        $contract = Contract::latest('id')->first();
        $this->assertEquals('BUY', $contract->type);

        // Attempt closing before goods are associated/sold
        $closeResponse = $this->actingAs($this->user)
            ->from('/contracts')
            ->post("/contracts/{$contract->id}/close");

        $closeResponse->assertRedirect('/contracts');
        $closeResponse->assertSessionHas('error');
        $contract->refresh();
        $this->assertEquals('ACTIVE', $contract->contractStatus);
    }

    public function test_can_cancel_contract()
    {
        $contract = Contract::create([
            'orderCode' => 'CANCEL-TEST-' . time(),
            'type' => 'SELL',
            'contractStatus' => 'ACTIVE',
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)->post("/contracts/{$contract->id}/cancel");
        $response->assertRedirect();

        $contract->refresh();
        $this->assertEquals('CANCELLED', $contract->contractStatus);

        $log = Log::where('module', 'CONTRACT')
            ->where('dataId', $contract->id)
            ->where('action', 'CANCEL')
            ->first();
        $this->assertNotNull($log);
    }

    public function test_can_add_comment_to_contract()
    {
        $contract = Contract::first();

        $response = $this->actingAs($this->user)->post("/contracts/{$contract->id}/comments", [
            'message' => 'Shipment inspection scheduled for next Monday.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'contract_id' => $contract->id,
            'message' => 'Shipment inspection scheduled for next Monday.',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_toggle_checklist_item()
    {
        $contract = Contract::first();
        $checklist = Checklist::firstOrCreate(
            ['contract_id' => $contract->id],
            ['name' => 'Compliance Review', 'active' => true]
        );

        $item = Checkitem::firstOrCreate(
            ['checklist_id' => $checklist->id, 'name' => 'Bill of Lading Verified'],
            ['checked' => false]
        );
        $item->checked = false;
        $item->save();

        $response = $this->actingAs($this->user)->post("/checkitems/{$item->id}/toggle");
        $response->assertStatus(200);
        $response->assertJson(['checked' => true]);

        $item->refresh();
        $this->assertTrue((bool)$item->checked);
    }
}
