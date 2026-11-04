<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Contact;
use Tests\TestCase;

class WebRoutesTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::create([
            'name' => 'Web Tester',
            'username' => 'webtester',
            'password' => bcrypt('password123'),
            'role' => 'ROLE_ADMIN',
            'active' => true,
        ]);
    }

    public function test_authenticated_dashboard_home()
    {
        $response = $this->actingAs($this->user)->get('/home');
        $response->assertStatus(200);
        $response->assertSee('Welcome back');
    }

    public function test_contracts_kanban_board_renders()
    {
        $response = $this->actingAs($this->user)->get('/contracts');
        $response->assertStatus(200);
        $response->assertSee('Contract Pipeline');
    }

    public function test_find_contracts_search_renders()
    {
        $response = $this->actingAs($this->user)->get('/findContracts');
        $response->assertStatus(200);
        $response->assertSee('Find Contracts');
    }

    public function test_contract_show_detail_renders()
    {
        $contract = \App\Models\Contract::first();
        if ($contract) {
            $response = $this->actingAs($this->user)->get('/contracts/' . $contract->id);
            $response->assertStatus(200);
            $response->assertSee('Contract Info');
            $response->assertSee('Documents');
        }
    }

    public function test_offers_screen_renders()
    {
        $response = $this->actingAs($this->user)->get('/offers');
        $response->assertStatus(200);
        $response->assertSee('Offers');
    }

    public function test_companies_index_and_show_render()
    {
        $company = Company::first();
        $this->assertNotNull($company);

        $responseIndex = $this->actingAs($this->user)->get('/companies');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Companies');

        $responseShow = $this->actingAs($this->user)->get("/companies/{$company->id}");
        $responseShow->assertStatus(200);
        $responseShow->assertSee($company->name);
    }

    public function test_contacts_index_and_show_render()
    {
        $contact = Contact::first();
        $this->assertNotNull($contact);

        $responseIndex = $this->actingAs($this->user)->get('/contacts');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Contacts');

        $responseShow = $this->actingAs($this->user)->get("/contacts/{$contact->id}");
        $responseShow->assertStatus(200);
        $responseShow->assertSee($contact->name);
    }

    public function test_products_catalog_renders()
    {
        $response = $this->actingAs($this->user)->get('/products');
        $response->assertStatus(200);
        $response->assertSee('Products');
    }

    public function test_users_management_renders()
    {
        $response = $this->actingAs($this->user)->get('/users');
        $response->assertStatus(200);
        $response->assertSee('Users');
    }

    public function test_access_groups_renders()
    {
        $response = $this->actingAs($this->user)->get('/accessgroups');
        $response->assertStatus(200);
        $response->assertSee('Access Control');
    }

    public function test_analytics_dashboard_renders()
    {
        $response = $this->actingAs($this->user)->get('/dashboards/analytics');
        $response->assertStatus(200);
        $response->assertSee('Metabase dashboard');
    }

    public function test_profile_settings_renders()
    {
        $response = $this->actingAs($this->user)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee('Security Settings');
    }

    public function test_settings_reference_tables_render()
    {
        $routes = [
            '/settings/countries',
            '/settings/currencies',
            '/settings/departments',
            '/settings/pack-types',
            '/settings/payment-types',
            '/settings/payment-terms',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->user)->get($route);
            $response->assertStatus(200);
        }
    }

    public function test_detached_notes_renders()
    {
        $response = $this->actingAs($this->user)->get('/detached-notes');
        $response->assertStatus(200);
        $response->assertSee('Detached Notes');
        $response->assertSee('There are no detachedNotes!');
    }

    public function test_templates_index_renders()
    {
        $response = $this->actingAs($this->user)->get('/templates');
        $response->assertStatus(200);
        $response->assertSee('Templates');
        $response->assertSee('ANNEX_VII');
        $response->assertSee('English (United Kingdom)');
    }

    public function test_templates_show_and_update()
    {
        $template = \App\Models\Template::where('name', 'ANNEX_VII')->first();
        if (!$template) {
            $template = \App\Models\Template::first();
        }

        $response = $this->actingAs($this->user)->get('/templates/' . $template->id);
        $response->assertStatus(200);
        $response->assertSee('Template Detail');
        $response->assertSee('HTML Code');

        $updateResponse = $this->actingAs($this->user)->put('/templates/' . $template->id, [
            'html' => '<html><body>Updated Annex VII</body></html>',
            'name' => $template->name,
            'type' => $template->type,
        ]);
        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('templates', [
            'id' => $template->id,
            'html' => '<html><body>Updated Annex VII</body></html>',
        ]);
    }

    public function test_countries_index_renders()
    {
        $response = $this->actingAs($this->user)->get('/countries');
        $response->assertStatus(200);
        $response->assertSee('Countries');
        $response->assertSee('Albania');
        $response->assertSee('Algeria');
    }

    public function test_country_create_and_store()
    {
        $response = $this->actingAs($this->user)->get('/countries/new');
        $response->assertStatus(200);
        $response->assertSee('New Country');
        $response->assertSee('Country Detail');
        $response->assertSee('Basic Info');

        $postResponse = $this->actingAs($this->user)->post('/countries', [
            'name' => 'Testland',
            'alpha2' => 'TL',
            'alpha3' => 'TLS',
            'region' => 'Europe',
            'vat' => 20,
        ]);
        $postResponse->assertRedirect();
        $this->assertDatabaseHas('countries', ['name' => 'Testland']);
    }

    public function test_company_types_index_and_create()
    {
        $response = $this->actingAs($this->user)->get('/company-types');
        $response->assertStatus(200);
        $response->assertSee('Company Types');
        $response->assertSee('Buyer');
        $response->assertSee('Seller');

        $createResponse = $this->actingAs($this->user)->get('/company-types/new');
        $createResponse->assertStatus(200);
        $createResponse->assertSee('New CompanyType');
        $createResponse->assertSee('Company Type Detail');
        $createResponse->assertSee('Basic Info');

        $postResponse = $this->actingAs($this->user)->post('/company-types', [
            'name' => 'Custom Broker',
        ]);
        $postResponse->assertRedirect();
        $this->assertDatabaseHas('types', ['description' => 'Custom Broker']);
    }

    public function test_credit_debit_types_index_and_create()
    {
        $response = $this->actingAs($this->user)->get('/credit-debit-types');
        $response->assertStatus(200);
        $response->assertSee('CreditDebitTypes');
        $response->assertSee('Delay');
        $response->assertSee('Container Damage');

        $createResponse = $this->actingAs($this->user)->get('/credit-debit-types/new');
        $createResponse->assertStatus(200);
        $createResponse->assertSee('New CreditDebitType');
        $createResponse->assertSee('CreditDebitType Detail');
        $createResponse->assertSee('Description *');

        $postResponse = $this->actingAs($this->user)->post('/credit-debit-types', [
            'description' => 'Test Fee Discrepancy',
        ]);
        $postResponse->assertRedirect();
        $this->assertDatabaseHas('creditdebittype', ['description' => 'Test Fee Discrepancy']);
    }

    public function test_currencies_index_and_create()
    {
        $response = $this->actingAs($this->user)->get('/currencies');
        $response->assertStatus(200);
        $response->assertSee('Currencies');
        $response->assertSee('United States Dollar');
        $response->assertSee('USD');

        $createResponse = $this->actingAs($this->user)->get('/currencies/new');
        $createResponse->assertStatus(200);
        $createResponse->assertSee('New Currency');
        $createResponse->assertSee('Currency Detail');
        $createResponse->assertSee('HTML encode Symbol *');

        $postResponse = $this->actingAs($this->user)->post('/currencies', [
            'name' => 'Japanese Yen',
            'abbreviation' => 'JPY',
            'symbol' => '¥',
            'htmlEncodeSymbol' => '&yen;',
        ]);
        $postResponse->assertRedirect();
        $this->assertDatabaseHas('currency', ['abbreviation' => 'JPY']);
    }

    public function test_departments_index_and_create()
    {
        $response = $this->actingAs($this->user)->get('/departments');
        $response->assertStatus(200);
        $response->assertSee('Departments');
        $response->assertSee('Sales');
        $response->assertSee('Marketing');

        $createResponse = $this->actingAs($this->user)->get('/departments/new');
        $createResponse->assertStatus(200);
        $createResponse->assertSee('New Department');
        $createResponse->assertSee('Department Detail');
        $createResponse->assertSee('Description *');

        $postResponse = $this->actingAs($this->user)->post('/departments', [
            'description' => 'Human Resources',
        ]);
        $postResponse->assertRedirect();
        $this->assertDatabaseHas('department', ['description' => 'Human Resources']);
    }

    public function test_pack_types_index_and_create()
    {
        $response = $this->actingAs($this->user)->get('/packTypes');
        $response->assertStatus(200);
        $response->assertSee('PackTypes');
        $response->assertSee('Big Bags');
        $response->assertSee('Pallets');

        $createResponse = $this->actingAs($this->user)->get('/packTypes/new');
        $createResponse->assertStatus(200);
        $createResponse->assertSee('New PackType');
        $createResponse->assertSee('PackType Detail');
        $createResponse->assertSee('Description *');

        $postResponse = $this->actingAs($this->user)->post('/packTypes', [
            'description' => 'Wooden Crates',
        ]);
        $postResponse->assertRedirect();
        $this->assertDatabaseHas('packtype', ['description' => 'Wooden Crates']);
    }

    public function test_payment_types_index_and_create()
    {
        $response = $this->actingAs($this->user)->get('/paymentTypes');
        $response->assertStatus(200);
        $response->assertSee('PaymentTypes');
        $response->assertSee('Sight LC');
        $response->assertSee('Deferred LC');

        $createResponse = $this->actingAs($this->user)->get('/paymentTypes/new');
        $createResponse->assertStatus(200);
        $createResponse->assertSee('New PaymentType');
        $createResponse->assertSee('PaymentType Detail');
        $createResponse->assertSee('Description *');

        $postResponse = $this->actingAs($this->user)->post('/paymentTypes', [
            'description' => 'Escrow Payment',
        ]);
        $postResponse->assertRedirect();
        $this->assertDatabaseHas('paymenttype', ['description' => 'Escrow Payment']);
    }

    public function test_payment_terms_index_and_create()
    {
        $response = $this->actingAs($this->user)->get('/paymentTermsTypes');
        $response->assertStatus(200);
        $response->assertSee('PaymentTermsTypes');
        $response->assertSee('Days');
        $response->assertSee('Months');

        $createResponse = $this->actingAs($this->user)->get('/paymentTermsTypes/new');
        $createResponse->assertStatus(200);
        $createResponse->assertSee('New PaymentTermsType');
        $createResponse->assertSee('PaymentTermsType Detail');
        $createResponse->assertSee('Description *');

        $postResponse = $this->actingAs($this->user)->post('/paymentTermsTypes', [
            'description' => 'Fortnightly',
        ]);
        $postResponse->assertRedirect();
        $this->assertDatabaseHas('paymenttermstype', ['description' => 'Fortnightly']);
    }
}