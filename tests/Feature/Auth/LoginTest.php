<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Enums\User\UserStatus;
use App\Enums\User\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login:127.0.0.1');
    }

    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token',
                ]
            ])
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'login_type' => 'email',
        ]);

        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_user_cannot_login_with_wrong_password()
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_user_cannot_login_with_non_existing_email()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/login', [
                'email' => 'nonexist@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(422);
    }

    public function test_blocked_user_cannot_login()
    {
        User::create([
            'name' => 'Blocked User',
            'email' => 'blocked@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::CLIENT,
            'status' => UserStatus::BLOCKED,
        ]);

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/login', [
                'email' => 'blocked@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_login_rate_limiting_works()
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->withHeaders(['Accept-Language' => 'uz'])
                ->postJson('/api/auth/login', [
                    'email' => 'test@example.com',
                    'password' => 'wrongpassword',
                ]);
        }

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(429);
    }

    public function test_fcm_token_is_updated_on_login()
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
                'fcm_token' => 'test_fcm_token_123',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'fcm_token' => 'test_fcm_token_123',
        ]);
    }

    public function test_email_is_required()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/login', [
                'password' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_password_is_required()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/login', [
                'email' => 'test@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}