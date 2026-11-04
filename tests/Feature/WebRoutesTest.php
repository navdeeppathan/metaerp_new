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
        $response->assertSee('Access Groups');
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
}
