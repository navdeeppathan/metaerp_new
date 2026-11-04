<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Contract;
use App\Models\Template;
use App\Models\GeneratedFile;
use App\Services\DocumentTemplateService;
use Tests\TestCase;

class DocumentGenerationTest extends TestCase
{
    protected User $user;
    protected Contract $contract;
    protected Template $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::create([
            'name' => 'Admin User',
            'username' => 'admin_doc',
            'password' => bcrypt('password123'),
            'role' => 'ROLE_ADMIN',
            'active' => true,
        ]);

        $this->contract = Contract::with(['purchase.metaCompany', 'purchase.contact', 'purchase.products.product'])
            ->first();

        $this->template = Template::firstOrCreate(
            ['name' => 'Purchase Contract Template'],
            [
                'type' => 'CONTRACT',
                'html' => '<html><body><h1>Contract {contract_order_code}</h1><p>Supplier: {meta_company_name}</p><p>Total: {contract_total_amount}</p></body></html>',
                'keys' => '{meta_company_name},{contract_order_code}',
            ]
        );
    }

    public function test_document_template_renders_and_replaces_tokens_successfully()
    {
        $service = app(DocumentTemplateService::class);
        $generated = $service->render($this->template, $this->contract, $this->user->id);

        $this->assertNotNull($generated);
        $this->assertInstanceOf(GeneratedFile::class, $generated);
        $this->assertEquals($this->contract->id, $generated->contract_id);
        $this->assertEquals($this->template->id, $generated->template_id);
        $this->assertStringContainsString($this->contract->orderCode, $generated->html);
        $this->assertStringContainsString('.html', $generated->fileName);

        $this->assertDatabaseHas('generatedfiles', [
            'id' => $generated->id,
            'contract_id' => $this->contract->id,
            'template_id' => $this->template->id,
        ]);
    }

    public function test_document_template_fails_when_required_key_is_missing()
    {
        $strictTemplate = Template::create([
            'name' => 'Strict Document Template ' . time(),
            'type' => 'INVOICE',
            'html' => '<div>Invoice: {missing_custom_token}</div>',
            'keys' => '{missing_custom_token}',
        ]);

        $service = app(DocumentTemplateService::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required keys not provided: {missing_custom_token}');

        $service->render($strictTemplate, $this->contract, $this->user->id);
    }

    public function test_web_endpoint_generates_document_and_redirects()
    {
        $response = $this->actingAs($this->user)
            ->from('/contracts')
            ->post("/contracts/{$this->contract->id}/generate-doc", [
                'template_id' => $this->template->id,
            ]);

        $response->assertRedirect('/contracts');
        $response->assertSessionHas('success');

        $latestFile = GeneratedFile::where('contract_id', $this->contract->id)
            ->where('template_id', $this->template->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($latestFile);
    }
}
