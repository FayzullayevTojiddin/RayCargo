<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateProfileImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function createAuthenticatedUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '998901234567',
            'password' => bcrypt('password123'),
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
        ], $overrides));
    }

    public function test_user_can_upload_profile_image()
    {
        $user = $this->createAuthenticatedUser();
        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/profile/image', [
                'image' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['image', 'image_url'],
            ]);

        $imagePath = $response->json('data.image');
        Storage::disk('public')->assertExists($imagePath);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'image' => $imagePath,
        ]);
    }

    public function test_old_image_is_deleted_when_uploading_new_one()
    {
        $user = $this->createAuthenticatedUser();

        // Birinchi rasm yuklash
        $firstFile = UploadedFile::fake()->image('first.jpg', 200, 200);
        $firstResponse = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/profile/image', [
                'image' => $firstFile,
            ]);

        $firstPath = $firstResponse->json('data.image');
        Storage::disk('public')->assertExists($firstPath);

        // Ikkinchi rasm yuklash
        $secondFile = UploadedFile::fake()->image('second.jpg', 200, 200);
        $secondResponse = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/profile/image', [
                'image' => $secondFile,
            ]);

        $secondPath = $secondResponse->json('data.image');

        // Eski rasm o'chirilgan, yangi rasm bor
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);
    }

    public function test_image_is_required()
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/profile/image', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_image_must_be_valid_format()
    {
        $user = $this->createAuthenticatedUser();
        $file = UploadedFile::fake()->create('document.pdf', 500);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/profile/image', [
                'image' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_image_must_not_exceed_2mb()
    {
        $user = $this->createAuthenticatedUser();
        $file = UploadedFile::fake()->image('large.jpg')->size(3000);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/profile/image', [
                'image' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_png_image_is_accepted()
    {
        $user = $this->createAuthenticatedUser();
        $file = UploadedFile::fake()->image('avatar.png', 200, 200);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/profile/image', [
                'image' => $file,
            ]);

        $response->assertStatus(200);
    }

    public function test_webp_image_is_accepted()
    {
        $user = $this->createAuthenticatedUser();
        $file = UploadedFile::fake()->image('avatar.webp', 200, 200);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/profile/image', [
                'image' => $file,
            ]);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_upload_image()
    {
        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/profile/image', [
                'image' => $file,
            ]);

        $response->assertStatus(401);
    }

    public function test_image_stored_in_users_directory()
    {
        $user = $this->createAuthenticatedUser();
        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/profile/image', [
                'image' => $file,
            ]);

        $imagePath = $response->json('data.image');
        $this->assertStringStartsWith('users/', $imagePath);
    }
}
