<?php

namespace Database\Seeders;

use App\Enums\Order\OrderStatus;
use App\Enums\User\UserRole;
use App\Models\CourierProfile;
use App\Models\Order;
use App\Models\User;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $clientIds = User::where('role', UserRole::CLIENT)
            ->limit(10)
            ->pluck('id')
            ->toArray();

        if (empty($clientIds)) {
            return;
        }

        $courierProfiles = CourierProfile::with('user')->get();
        
        if ($courierProfiles->isEmpty()) {
            return;
        }

        $vehicleTypeIds = VehicleType::pluck('id')->toArray();
        
        if (empty($vehicleTypeIds)) {
            return;
        }

        $statuses = OrderStatus::cases();

        $orders = [];
        for ($i = 0; $i < 50; $i++) {
            $status = $statuses[array_rand($statuses)];
            $courierProfile = $courierProfiles->random();
            
            $orders[] = [
                'client_id' => $clientIds[array_rand($clientIds)],
                'courier_id' => $courierProfile->user_id,
                'vehicle_type_id' => $vehicleTypeIds[array_rand($vehicleTypeIds)],
                'status' => $status->value,
                'total_distance_km' => rand(5, 500) / 10,
                'total_price' => rand(10000, 500000),
                'created_at' => now()->subDays(rand(0, 30)),
                'updated_at' => now()->subDays(rand(0, 30)),
            ];
        }

        DB::table('orders')->insert($orders);
    }
}