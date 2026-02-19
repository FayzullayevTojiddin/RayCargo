<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    private function createAuthenticatedUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '998901234567',
            'password' => bcrypt('password123'),
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
            'lang' => 'uz',
        ], $overrides));
    }

    public function test_user_can_update_name()
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->putJson('/api/profile', [
                'name' => 'Yangi Ism',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Yangi Ism',
        ]);
    }

    public function test_user_can_update_phone_number()
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->putJson('/api/profile', [
                'phone_number' => '998901111111',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone_number' => '998901111111',
        ]);
    }

    public function test_user_can_update_lang()
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->putJson('/api/profile', [
                'lang' => 'ru',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'lang' => 'ru',
        ]);
    }

    public function test_user_can_update_multiple_fields()
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->putJson('/api/profile', [
                'name' => 'Yangi Ism',
                'phone_number' => '998902222222',
                'lang' => 'en',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user'],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Yangi Ism',
            'phone_number' => '998902222222',
            'lang' => 'en',
        ]);
    }

    public function test_phone_number_must_be_unique()
    {
        $this->createAuthenticatedUser([
            'email' => 'other@example.com',
            'phone_number' => '998901111111',
        ]);

        $user = $this->createAuthenticatedUser([
            'phone_number' => '998902222222',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->putJson('/api/profile', [
                'phone_number' => '998901111111',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);
    }

    public function test_user_can_keep_own_phone_number()
    {
        $user = $this->createAuthenticatedUser([
            'phone_number' => '998901234567',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->putJson('/api/profile', [
                'phone_number' => '998901234567',
                'name' => 'Yangilangan',
            ]);

        $response->assertStatus(200);
    }

    public function test_lang_must_be_valid()
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->putJson('/api/profile', [
                'lang' => 'fr',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lang']);
    }

    public function test_unauthenticated_user_cannot_update_profile()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->putJson('/api/profile', [
                'name' => 'Yangi Ism',
            ]);

        $response->assertStatus(401);
    }

    public function test_response_contains_updated_user_data()
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->putJson('/api/profile', [
                'name' => 'Yangilangan Ism',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.user.name', 'Yangilangan Ism');
    }
}
