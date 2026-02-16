<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Enums\User\UserStatus;
use App\Enums\User\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('register:127.0.0.1');
        RateLimiter::clear('verify:127.0.0.1');
        Cache::flush();
    }

    public function test_user_can_register_with_valid_data()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone_number' => '998901234567',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'verification_id',
                    'expires_in',
                ]
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_email_must_be_unique()
    {
        User::create([
            'name' => 'Existing User',
            'email' => 'test@example.com',
            'phone_number' => '998901111111',
            'password' => bcrypt('password123'),
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone_number' => '998901234567',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_phone_number_must_be_unique()
    {
        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'phone_number' => '998901234567',
            'password' => bcrypt('password123'),
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone_number' => '998901234567',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);
    }

    public function test_password_confirmation_must_match()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone_number' => '998901234567',
                'password' => 'password123',
                'password_confirmation' => 'differentpassword',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_all_fields_are_required()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'phone_number', 'password']);
    }

    public function test_register_rate_limiting_works()
    {
        for ($i = 0; $i < 3; $i++) {
            $this->withHeaders(['Accept-Language' => 'uz'])
                ->postJson('/api/auth/register', [
                    'name' => 'Test User',
                    'email' => "test{$i}@example.com",
                    'phone_number' => "99890123456{$i}",
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                ]);
        }

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register', [
                'name' => 'Test User',
                'email' => 'test4@example.com',
                'phone_number' => '998901234564',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(429);
    }

    public function test_verification_code_stored_in_cache()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone_number' => '998901234567',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $verificationId = $response->json('data.verification_id');
        $cacheKey = "register:verify:{$verificationId}";
        
        $this->assertTrue(Cache::has($cacheKey));
        
        $cachedData = Cache::get($cacheKey);
        $this->assertEquals('Test User', $cachedData['name']);
        $this->assertEquals('test@example.com', $cachedData['email']);
        $this->assertArrayHasKey('code', $cachedData);
    }

    public function test_can_verify_registration_with_correct_code()
    {
        $registerResponse = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone_number' => '998901234567',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $verificationId = $registerResponse->json('data.verification_id');
        $cacheKey = "register:verify:{$verificationId}";
        $cachedData = Cache::get($cacheKey);
        $code = $cachedData['code'];

        $verifyResponse = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register/verify', [
                'verification_id' => $verificationId,
                'code' => $code,
            ]);

        $verifyResponse->assertStatus(200)
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
            'name' => 'Test User',
        ]);

        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_cannot_verify_with_incorrect_code()
    {
        $registerResponse = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone_number' => '998901234567',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $verificationId = $registerResponse->json('data.verification_id');

        $verifyResponse = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register/verify', [
                'verification_id' => $verificationId,
                'code' => '999999',
            ]);

        $verifyResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_cannot_verify_with_expired_verification_id()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register/verify', [
                'verification_id' => 'invalid_id',
                'code' => '123456',
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_verify_rate_limiting_works()
    {
        $registerResponse = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone_number' => '998901234567',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $verificationId = $registerResponse->json('data.verification_id');

        for ($i = 0; $i < 5; $i++) {
            $this->withHeaders(['Accept-Language' => 'uz'])
                ->postJson('/api/auth/register/verify', [
                    'verification_id' => $verificationId,
                    'code' => '999999',
                ]);
        }

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register/verify', [
                'verification_id' => $verificationId,
                'code' => '999999',
            ]);

        $response->assertStatus(429);
    }

    public function test_can_resend_verification_code()
    {
        $registerResponse = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone_number' => '998901234567',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $verificationId = $registerResponse->json('data.verification_id');
        $cacheKey = "register:verify:{$verificationId}";
        $oldCode = Cache::get($cacheKey)['code'];

        $resendResponse = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register/resend', [
                'verification_id' => $verificationId,
            ]);

        $resendResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $newCode = Cache::get($cacheKey)['code'];
        $this->assertNotEquals($oldCode, $newCode);
    }

    public function test_resend_rate_limiting_works()
    {
        $registerResponse = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone_number' => '998901234567',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $verificationId = $registerResponse->json('data.verification_id');

        for ($i = 0; $i < 3; $i++) {
            $this->withHeaders(['Accept-Language' => 'uz'])
                ->postJson('/api/auth/register/resend', [
                    'verification_id' => $verificationId,
                ]);
        }

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register/resend', [
                'verification_id' => $verificationId,
            ]);

        $response->assertStatus(429);
    }

    public function test_cannot_resend_with_invalid_verification_id()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/auth/register/resend', [
                'verification_id' => 'invalid_id',
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }
}