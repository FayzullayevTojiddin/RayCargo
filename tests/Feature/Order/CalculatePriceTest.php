<?php

namespace Tests\Feature\Order;

use App\Models\User;
use App\Models\VehicleType;
use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatePriceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test Client',
            'email' => 'client@example.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
        ]);

        $this->seedVehicleTypes();
    }

    private function seedVehicleTypes(): void
    {
        VehicleType::create([
            'title' => 'Kichik',
            'status' => true,
            'max_weight_kg' => 100,
            'base_price' => 15000,
            'price_per_kg' => 500,
            'price_per_km' => 1000,
            'price_per_hour' => 15000,
            'is_active' => true,
        ]);

        VehicleType::create([
            'title' => 'Katta',
            'status' => true,
            'max_weight_kg' => 2000,
            'base_price' => 40000,
            'price_per_kg' => 100,
            'price_per_km' => 2000,
            'price_per_hour' => 40000,
            'is_active' => true,
        ]);

        VehicleType::create([
            'title' => 'Noaktiv',
            'status' => false,
            'max_weight_kg' => 50,
            'base_price' => 5000,
            'price_per_kg' => 200,
            'price_per_km' => 500,
            'price_per_hour' => 8000,
            'is_active' => false,
        ]);
    }

    public function test_can_calculate_price_with_two_stops()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders/calculate-price', [
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401],
                    ['lat' => 41.3111, 'lng' => 69.2797],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total_distance_km',
                    'stops_count',
                    'vehicle_types' => [
                        '*' => [
                            'id',
                            'title',
                            'icon',
                            'max_weight_kg',
                            'price_breakdown' => [
                                'base_price',
                                'delivery_price',
                                'price_per_km',
                                'distance_km',
                            ],
                            'total_price',
                        ]
                    ]
                ]
            ]);

        $data = $response->json('data');

        $this->assertEquals(2, $data['stops_count']);
        $this->assertGreaterThan(0, $data['total_distance_km']);
        // Faqat aktiv transport turlari qaytishi kerak (2 ta)
        $this->assertCount(2, $data['vehicle_types']);
    }

    public function test_can_calculate_price_with_multiple_stops()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders/calculate-price', [
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401],
                    ['lat' => 41.3111, 'lng' => 69.2797],
                    ['lat' => 41.3260, 'lng' => 69.2285],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertEquals(3, $data['stops_count']);
    }

    public function test_stops_are_required()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders/calculate-price', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stops']);
    }

    public function test_minimum_two_stops_required()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders/calculate-price', [
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stops']);
    }

    public function test_lat_lng_are_required_for_each_stop()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders/calculate-price', [
                'stops' => [
                    ['lat' => 41.2995],
                    ['lng' => 69.2797],
                ],
            ]);

        $response->assertStatus(422);
    }

    public function test_lat_must_be_valid_range()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders/calculate-price', [
                'stops' => [
                    ['lat' => 91, 'lng' => 69.2401],
                    ['lat' => 41.3111, 'lng' => 69.2797],
                ],
            ]);

        $response->assertStatus(422);
    }

    public function test_inactive_vehicle_types_not_returned()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders/calculate-price', [
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401],
                    ['lat' => 41.3111, 'lng' => 69.2797],
                ],
            ]);

        $titles = collect($response->json('data.vehicle_types'))->pluck('title')->toArray();
        $this->assertNotContains('Noaktiv', $titles);
    }

    public function test_vehicle_types_ordered_by_weight()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders/calculate-price', [
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401],
                    ['lat' => 41.3111, 'lng' => 69.2797],
                ],
            ]);

        $weights = collect($response->json('data.vehicle_types'))->pluck('max_weight_kg')->toArray();
        $sorted = $weights;
        sort($sorted);
        $this->assertEquals($sorted, $weights);
    }

    public function test_bigger_vehicle_has_higher_price()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders/calculate-price', [
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401],
                    ['lat' => 41.3111, 'lng' => 69.2797],
                ],
            ]);

        $prices = collect($response->json('data.vehicle_types'))->pluck('total_price')->toArray();
        $this->assertGreaterThan($prices[0], $prices[1]);
    }

    public function test_unauthenticated_user_cannot_calculate()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders/calculate-price', [
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401],
                    ['lat' => 41.3111, 'lng' => 69.2797],
                ],
            ]);

        $response->assertStatus(401);
    }
}
