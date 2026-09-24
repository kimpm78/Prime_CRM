<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login_read_profile_and_logout(): void
    {
        $user = User::factory()->create([
            'name' => '認証テスト管理者',
            'email' => 'admin@example.com',
            'password' => 'Secret123!',
            'is_active' => true,
        ]);

        $this->withHeader('Origin', 'http://localhost:5173')
            ->postJson('/api/auth/login', [
                'email' => 'admin@example.com',
                'password' => 'Secret123!',
            ])
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.name', '認証テスト管理者');

        $this->getJson('/api/auth/user')->assertOk()->assertJsonPath('user.email', 'admin@example.com');
        $this->postJson('/api/auth/logout')->assertOk();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/auth/user')->assertUnauthorized();
    }

    public function test_invalid_or_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => 'Secret123!',
            'is_active' => false,
        ]);

        $this->withHeader('Origin', 'http://localhost:5173')
            ->postJson('/api/auth/login', [
                'email' => 'inactive@example.com',
                'password' => 'Secret123!',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_crm_api_requires_authentication(): void
    {
        $this->getJson('/api/dashboard')->assertUnauthorized();
        $this->getJson('/api/accounts')->assertUnauthorized();
        $this->getJson('/api/notifications')->assertUnauthorized();
    }
}
