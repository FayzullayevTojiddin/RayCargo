<?php

namespace Database\Seeders;

use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use App\Models\User;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourierProfileSeeder extends Seeder
{
    public function run(): void
    {
        $vehicleTypeIds = VehicleType::pluck('id')->toArray();

        if (empty($vehicleTypeIds)) {
            return;
        }

        $courierUsers = User::where('role', UserRole::COURIER)
            ->limit(20)
            ->pluck('id')
            ->toArray();

        if (empty($courierUsers)) {
            $users = [];
            for ($i = 1; $i <= 20; $i++) {
                $users[] = [
                    'name' => fake()->name(),
                    'email' => "courier$i@raycargo.uz",
                    'phone_number' => '+998901234' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'password' => bcrypt('password'),
                    'role' => UserRole::COURIER->value,
                    'status' => UserStatus::ACTIVE->value,
                    'lang' => 'uz',
                    'last_seen_at' => now()->subMinutes(rand(0, 60)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('users')->insert($users);
            $courierUsers = User::where('role', UserRole::COURIER)
                ->whereIn('email', array_column($users, 'email'))
                ->pluck('id')
                ->toArray();
        }

        $profiles = [];
        foreach ($courierUsers as $userId) {
            $isPedestrian = (bool) rand(0, 3) === 0; // 25% piyoda

            $profiles[] = [
                'user_id' => $userId,
                'is_pedestrian' => $isPedestrian,
                'vehicle_type_id' => $isPedestrian ? null : $vehicleTypeIds[array_rand($vehicleTypeIds)],
                'vehicle_number' => $isPedestrian ? null : rand(10, 99) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . rand(100, 999),
                'vehicle_brand' => $isPedestrian ? null : fake()->randomElement(['Chevrolet', 'Kia', 'Hyundai', 'Toyota', 'Daewoo']),
                'vehicle_model' => $isPedestrian ? null : fake()->randomElement(['Cobalt', 'Spark', 'Malibu', 'Rio', 'Accent']),
                'vehicle_color' => $isPedestrian ? null : fake()->randomElement(['Oq', 'Qora', 'Kumush', 'Ko\'k', 'Qizil']),
                'license_number' => 'AB' . rand(1000000, 9999999),
                'rating' => rand(35, 50) / 10,
                'is_online' => (bool) rand(0, 1),
                'is_active' => true,
                'application_status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('courier_profiles')->insert($profiles);
    }
}
