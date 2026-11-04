<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Contract;
use App\Models\Product;
use App\Models\Company;
use App\Models\ModelWorkflow;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiCompatibilityTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::where('username', 'admin')->first();
        if (!$this->user) {
            $this->user = User::create([
                'name' => 'Admin API User',
                'displayName' => 'Administrator',
                'username' => 'admin',
                'password' => bcrypt('password123'),
                'role' => 'ROLE_ADMIN',
                'enable2fa' => false,
                'active' => true,
            ]);
        } else {
            $this->user->enable2fa = false;
            $this->user->password = bcrypt('password123');
            $this->user->save();
        }
    }

    public function test_api_login_returns_token_and_user_payload()
    {
        $response = $this->postJson('/erp-api/login', [
            'username' => 'admin',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token',
            'user' => [
                'id',
                'name',
                'displayName',
                'role',
            ],
        ]);

        $this->assertEquals($this->user->id, $response->json('user.id'));
    }

    public function test_api_login_fails_with_invalid_credentials()
    {
        $response = $this->postJson('/erp-api/login', [
            'username' => 'admin',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401);
    }

    public function test_dual_prefix_routes_erp_api_and_api()
    {
        Sanctum::actingAs($this->user);

        // Test /erp-api/products
        $res1 = $this->getJson('/erp-api/products');
        $res1->assertStatus(200);

        // Test /api/products
        $res2 = $this->getJson('/api/products');
        $res2->assertStatus(200);

        $this->assertIsArray($res1->json('content'));
        $this->assertIsArray($res2->json('content'));
    }

    public function test_scrumboard_model_with_permissions_endpoint()
    {
        Sanctum::actingAs($this->user);

        $model = ModelWorkflow::first();
        $this->assertNotNull($model);

        $response = $this->getJson("/erp-api/models/withpermissions/{$model->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'title',
            'lists',
            'cards',
        ]);
    }

    public function test_api_companies_endpoint_returns_spring_compatible_pagination()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/erp-api/companies');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'content',
            'totalElements',
            'totalPages',
            'size',
            'number',
        ]);
    }

    public function test_api_contracts_endpoint()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/erp-api/contracts');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'content',
            'totalElements',
        ]);
    }
}
