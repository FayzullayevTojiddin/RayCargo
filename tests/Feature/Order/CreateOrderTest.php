<?php

namespace Tests\Feature\Order;

use App\Models\User;
use App\Models\VehicleType;
use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private VehicleType $vehicleType;

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

        $this->vehicleType = VehicleType::create([
            'title' => 'Kichik',
            'status' => true,
            'max_weight_kg' => 100,
            'base_price' => 15000,
            'price_per_kg' => 500,
            'price_per_km' => 1000,
            'price_per_hour' => 15000,
            'is_active' => true,
        ]);
    }

    public function test_can_create_order_with_two_stops()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders', [
                'vehicle_type_id' => $this->vehicleType->id,
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401, 'type' => 'pickup', 'address_text' => 'Toshkent A'],
                    ['lat' => 41.3111, 'lng' => 69.2797, 'type' => 'dropoff', 'address_text' => 'Toshkent B'],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'order' => [
                        'id',
                        'client_id',
                        'vehicle_type_id',
                        'status',
                        'total_distance_km',
                        'total_price',
                        'stops',
                        'items',
                        'vehicle_type',
                    ]
                ]
            ]);

        $order = $response->json('data.order');
        $this->assertEquals($this->user->id, $order['client_id']);
        $this->assertEquals('created', $order['status']);
        $this->assertCount(2, $order['stops']);
        $this->assertGreaterThanOrEqual(2, count($order['items']));
    }

    public function test_can_create_order_with_multiple_stops()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders', [
                'vehicle_type_id' => $this->vehicleType->id,
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401, 'type' => 'pickup'],
                    ['lat' => 41.3111, 'lng' => 69.2797, 'type' => 'dropoff'],
                    ['lat' => 41.3260, 'lng' => 69.2285, 'type' => 'dropoff'],
                ],
            ]);

        $response->assertStatus(201);
        $this->assertCount(3, $response->json('data.order.stops'));
    }

    public function test_stops_saved_with_correct_sequence()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders', [
                'vehicle_type_id' => $this->vehicleType->id,
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401, 'type' => 'pickup'],
                    ['lat' => 41.3111, 'lng' => 69.2797, 'type' => 'dropoff'],
                    ['lat' => 41.3260, 'lng' => 69.2285, 'type' => 'dropoff'],
                ],
            ]);

        $stops = $response->json('data.order.stops');
        $this->assertEquals(1, $stops[0]['sequence']);
        $this->assertEquals(2, $stops[1]['sequence']);
        $this->assertEquals(3, $stops[2]['sequence']);
    }

    public function test_order_has_price_items()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders', [
                'vehicle_type_id' => $this->vehicleType->id,
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401, 'type' => 'pickup'],
                    ['lat' => 41.3111, 'lng' => 69.2797, 'type' => 'dropoff'],
                ],
            ]);

        $items = $response->json('data.order.items');
        $types = collect($items)->pluck('type')->toArray();

        $this->assertContains('base', $types);
        $this->assertContains('delivery', $types);
    }

    public function test_total_price_equals_sum_of_items()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders', [
                'vehicle_type_id' => $this->vehicleType->id,
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401, 'type' => 'pickup'],
                    ['lat' => 41.3111, 'lng' => 69.2797, 'type' => 'dropoff'],
                ],
            ]);

        $order = $response->json('data.order');
        $itemsSum = collect($order['items'])->sum('amount');

        $this->assertEquals((int) $order['total_price'], $itemsSum);
    }

    public function test_order_saved_in_database()
    {
        $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders', [
                'vehicle_type_id' => $this->vehicleType->id,
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401, 'type' => 'pickup'],
                    ['lat' => 41.3111, 'lng' => 69.2797, 'type' => 'dropoff'],
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'client_id' => $this->user->id,
            'vehicle_type_id' => $this->vehicleType->id,
            'status' => 'created',
        ]);

        $this->assertDatabaseCount('order_stops', 2);
        $this->assertDatabaseCount('order_price_items', 2);
    }

    public function test_vehicle_type_id_required()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders', [
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401, 'type' => 'pickup'],
                    ['lat' => 41.3111, 'lng' => 69.2797, 'type' => 'dropoff'],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vehicle_type_id']);
    }

    public function test_vehicle_type_must_exist()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders', [
                'vehicle_type_id' => 9999,
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401, 'type' => 'pickup'],
                    ['lat' => 41.3111, 'lng' => 69.2797, 'type' => 'dropoff'],
                ],
            ]);

        $response->assertStatus(422);
    }

    public function test_inactive_vehicle_type_rejected()
    {
        $inactive = VehicleType::create([
            'title' => 'Noaktiv',
            'status' => false,
            'max_weight_kg' => 50,
            'base_price' => 5000,
            'price_per_kg' => 200,
            'price_per_km' => 500,
            'price_per_hour' => 8000,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders', [
                'vehicle_type_id' => $inactive->id,
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401, 'type' => 'pickup'],
                    ['lat' => 41.3111, 'lng' => 69.2797, 'type' => 'dropoff'],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_stop_type_is_required()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders', [
                'vehicle_type_id' => $this->vehicleType->id,
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401],
                    ['lat' => 41.3111, 'lng' => 69.2797],
                ],
            ]);

        $response->assertStatus(422);
    }

    public function test_stop_type_must_be_valid()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders', [
                'vehicle_type_id' => $this->vehicleType->id,
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401, 'type' => 'invalid'],
                    ['lat' => 41.3111, 'lng' => 69.2797, 'type' => 'dropoff'],
                ],
            ]);

        $response->assertStatus(422);
    }

    public function test_unauthenticated_user_cannot_create_order()
    {
        $response = $this->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders', [
                'vehicle_type_id' => $this->vehicleType->id,
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401, 'type' => 'pickup'],
                    ['lat' => 41.3111, 'lng' => 69.2797, 'type' => 'dropoff'],
                ],
            ]);

        $response->assertStatus(401);
    }

    public function test_address_text_saved_correctly()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['Accept-Language' => 'uz'])
            ->postJson('/api/orders', [
                'vehicle_type_id' => $this->vehicleType->id,
                'stops' => [
                    ['lat' => 41.2995, 'lng' => 69.2401, 'type' => 'pickup', 'address_text' => 'Chilonzor 9'],
                    ['lat' => 41.3111, 'lng' => 69.2797, 'type' => 'dropoff', 'address_text' => 'Sergeli 7'],
                ],
            ]);

        $this->assertDatabaseHas('order_stops', ['address_text' => 'Chilonzor 9']);
        $this->assertDatabaseHas('order_stops', ['address_text' => 'Sergeli 7']);
    }
}
