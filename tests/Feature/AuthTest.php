<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TotpService;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_guest_cannot_access_dashboard()
    {
        $response = $this->get('/home');
        $response->assertRedirect('/login');
    }

    public function test_user_can_view_login_page()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('MetaERP');
    }

    public function test_user_authentication_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'wrongpassword123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_user_authentication_flow()
    {
        $user = User::where('username', 'testuser')->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'displayName' => 'Test User',
                'username' => 'testuser',
                'password' => bcrypt('password123'),
                'enable2fa' => false,
                'active' => true,
            ]);
        } else {
            $user->enable2fa = false;
            $user->password = bcrypt('password123');
            $user->save();
        }

        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_with_2fa_redirects_to_challenge()
    {
        $user = User::where('username', 'admin')->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin User',
                'displayName' => 'Administrator',
                'username' => 'admin',
                'password' => bcrypt('password123'),
                'enable2fa' => true,
                'secret' => 'JBSWY3DPEHPK3PXP',
                'active' => true,
            ]);
        } else {
            $user->enable2fa = true;
            $user->password = bcrypt('password123');
            $user->secret = 'JBSWY3DPEHPK3PXP';
            $user->save();
        }

        $response = $this->from('/login')->post('/login', [
            'username' => 'admin',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/two-factor-challenge');
        $response->assertSessionHas('2fa:user:id', $user->id);
    }

    public function test_2fa_challenge_verification_with_valid_totp()
    {
        $user = User::where('username', 'admin')->first();
        $totpService = app(TotpService::class);
        $validCode = $totpService->generateCode($user->secret ?? 'JBSWY3DPEHPK3PXP');

        $response = $this->withSession(['2fa:user:id' => $user->id])
            ->post('/two-factor-challenge', [
                'code' => $validCode,
            ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_2fa_challenge_fails_with_invalid_totp()
    {
        $user = User::where('username', 'admin')->first();

        $response = $this->withSession(['2fa:user:id' => $user->id])
            ->post('/two-factor-challenge', [
                'code' => '000000',
            ]);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout()
    {
        $user = User::first();
        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
