<?php

namespace Tests\Feature\Courier\Auth;

use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class CourierAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        RateLimiter::clear('courier:register:127.0.0.1');
        RateLimiter::clear('courier:login:127.0.0.1');
        RateLimiter::clear('courier:verify:127.0.0.1');
    }

    // ==================== REGISTER ====================

    public function test_courier_can_register_with_email()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/register', [
                'login' => 'courier@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['verification_id', 'login_type', 'expires_in'],
            ]);

        $this->assertEquals('email', $response->json('data.login_type'));
    }

    public function test_courier_can_register_with_phone()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/register', [
                'login' => '+998901234567',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals('phone', $response->json('data.login_type'));
    }

    public function test_courier_register_requires_password_confirmation()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/register', [
                'login' => 'courier@example.com',
                'password' => 'password123',
                'password_confirmation' => 'different',
            ]);

        $response->assertStatus(422);
    }

    public function test_courier_register_email_must_be_unique()
    {
        User::create([
            'name' => 'Existing Courier',
            'email' => 'courier@example.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::COURIER,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/register', [
                'login' => 'courier@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(422);
    }

    public function test_courier_register_phone_must_be_unique()
    {
        User::create([
            'name' => 'Existing Courier',
            'phone_number' => '+998901234567',
            'password' => bcrypt('password123'),
            'role' => UserRole::COURIER,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/register', [
                'login' => '+998901234567',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(422);
    }

    public function test_courier_register_stores_verification_in_cache()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/register', [
                'login' => 'courier@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $verificationId = $response->json('data.verification_id');
        $cacheKey = "courier:register:verify:{$verificationId}";

        $this->assertTrue(Cache::has($cacheKey));

        $cached = Cache::get($cacheKey);
        $this->assertEquals('courier@example.com', $cached['login']);
        $this->assertEquals('email', $cached['login_type']);
        $this->assertArrayHasKey('code', $cached);
    }

    public function test_courier_register_rate_limiting()
    {
        for ($i = 0; $i < 3; $i++) {
            $this->withHeaders(['Accept-Language' => 'uz'])
                ->postJson('/api/courier/auth/register', [
                    'login' => "courier{$i}@example.com",
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                ]);
        }

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/register', [
                'login' => 'courier99@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(429);
    }

    // ==================== VERIFY REGISTER ====================

    public function test_courier_can_verify_registration_with_email()
    {
        $registerResponse = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/register', [
                'login' => 'courier@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $verificationId = $registerResponse->json('data.verification_id');
        $code = Cache::get("courier:register:verify:{$verificationId}")['code'];

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/verify', [
                'verification_id' => $verificationId,
                'code' => $code,
                'type' => 'register',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => ['user', 'token', 'status' => ['is_active', 'is_online', 'has_profile']],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'courier@example.com',
            'role' => UserRole::COURIER->value,
        ]);
    }

    public function test_courier_can_verify_registration_with_phone()
    {
        $registerResponse = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/register', [
                'login' => '+998901234567',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $verificationId = $registerResponse->json('data.verification_id');
        $code = Cache::get("courier:register:verify:{$verificationId}")['code'];

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/verify', [
                'verification_id' => $verificationId,
                'code' => $code,
                'type' => 'register',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'phone_number' => '+998901234567',
            'role' => UserRole::COURIER->value,
        ]);
    }

    public function test_courier_verify_returns_status()
    {
        $registerResponse = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/register', [
                'login' => 'courier@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $verificationId = $registerResponse->json('data.verification_id');
        $code = Cache::get("courier:register:verify:{$verificationId}")['code'];

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/verify', [
                'verification_id' => $verificationId,
                'code' => $code,
                'type' => 'register',
            ]);

        $status = $response->json('data.status');
        $this->assertFalse($status['is_active']);
        $this->assertFalse($status['is_online']);
        $this->assertFalse($status['has_profile']);
    }

    public function test_courier_verify_fails_with_wrong_code()
    {
        $registerResponse = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/register', [
                'login' => 'courier@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $verificationId = $registerResponse->json('data.verification_id');

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/verify', [
                'verification_id' => $verificationId,
                'code' => '000000',
                'type' => 'register',
            ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_courier_verify_fails_with_expired_id()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/verify', [
                'verification_id' => 'nonexistent',
                'code' => '123456',
                'type' => 'register',
            ]);

        $response->assertStatus(400);
    }

    // ==================== LOGIN ====================

    public function test_courier_can_login_with_email()
    {
        $user = User::create([
            'name' => 'Courier User',
            'email' => 'courier@example.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::COURIER,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/login', [
                'login' => 'courier@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => ['verification_id', 'login_type', 'expires_in'],
            ]);

        $this->assertEquals('email', $response->json('data.login_type'));
    }

    public function test_courier_can_login_with_phone()
    {
        User::create([
            'name' => 'Courier User',
            'phone_number' => '+998901234567',
            'password' => bcrypt('password123'),
            'role' => UserRole::COURIER,
            'status' => UserStatus::ACTIVE,
            'login_type' => 'phone',
        ]);

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/login', [
                'login' => '+998901234567',
                'password' => 'password123',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals('phone', $response->json('data.login_type'));
    }

    public function test_courier_login_fails_with_wrong_password()
    {
        User::create([
            'name' => 'Courier User',
            'email' => 'courier@example.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::COURIER,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/login', [
                'login' => 'courier@example.com',
                'password' => 'wrongpassword',
            ]);

        $response->assertStatus(401);
    }

    public function test_courier_login_fails_for_non_courier_user()
    {
        User::create([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/login', [
                'login' => 'client@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(401);
    }

    public function test_courier_login_fails_for_blocked_user()
    {
        User::create([
            'name' => 'Blocked Courier',
            'email' => 'courier@example.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::COURIER,
            'status' => UserStatus::BLOCKED,
        ]);

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/login', [
                'login' => 'courier@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(403);
    }

    public function test_courier_can_verify_login()
    {
        $user = User::create([
            'name' => 'Courier User',
            'email' => 'courier@example.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::COURIER,
            'status' => UserStatus::ACTIVE,
        ]);

        $loginResponse = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/login', [
                'login' => 'courier@example.com',
                'password' => 'password123',
            ]);

        $verificationId = $loginResponse->json('data.verification_id');
        $code = Cache::get("courier:login:verify:{$verificationId}")['code'];

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/verify', [
                'verification_id' => $verificationId,
                'code' => $code,
                'type' => 'login',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => ['user', 'token', 'status'],
            ]);
    }

    // ==================== RESEND ====================

    public function test_courier_can_resend_register_code()
    {
        $registerResponse = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/register', [
                'login' => 'courier@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $verificationId = $registerResponse->json('data.verification_id');
        $oldCode = Cache::get("courier:register:verify:{$verificationId}")['code'];

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/resend', [
                'verification_id' => $verificationId,
                'type' => 'register',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $newCode = Cache::get("courier:register:verify:{$verificationId}")['code'];
        $this->assertNotEquals($oldCode, $newCode);
    }

    public function test_courier_can_resend_login_code()
    {
        User::create([
            'name' => 'Courier User',
            'email' => 'courier@example.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::COURIER,
            'status' => UserStatus::ACTIVE,
        ]);

        $loginResponse = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/login', [
                'login' => 'courier@example.com',
                'password' => 'password123',
            ]);

        $verificationId = $loginResponse->json('data.verification_id');
        $oldCode = Cache::get("courier:login:verify:{$verificationId}")['code'];

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/resend', [
                'verification_id' => $verificationId,
                'type' => 'login',
            ]);

        $response->assertStatus(200);

        $newCode = Cache::get("courier:login:verify:{$verificationId}")['code'];
        $this->assertNotEquals($oldCode, $newCode);
    }

    public function test_courier_resend_fails_with_invalid_id()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/courier/auth/resend', [
                'verification_id' => 'nonexistent',
                'type' => 'register',
            ]);

        $response->assertStatus(400);
    }
}
