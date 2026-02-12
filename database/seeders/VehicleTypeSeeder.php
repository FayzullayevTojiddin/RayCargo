<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleTypeSeeder extends Seeder
{
    public function run(): void
    {
        $vehicleTypes = [
            [
                'title' => 'Piyoda',
                'status' => true,
                'max_weight_kg' => 5,
                'base_price' => 5000,
                'price_per_kg' => 1000,
                'price_per_km' => 2000,
                'price_per_hour' => 10000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Velosiped',
                'status' => true,
                'max_weight_kg' => 15,
                'base_price' => 8000,
                'price_per_kg' => 800,
                'price_per_km' => 1500,
                'price_per_hour' => 12000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Mototsikl',
                'status' => true,
                'max_weight_kg' => 30,
                'base_price' => 15000,
                'price_per_kg' => 500,
                'price_per_km' => 1000,
                'price_per_hour' => 15000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Avtomobil (Sedan)',
                'status' => true,
                'max_weight_kg' => 100,
                'base_price' => 25000,
                'price_per_kg' => 300,
                'price_per_km' => 800,
                'price_per_hour' => 20000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Yengil yuk mashinasi',
                'status' => true,
                'max_weight_kg' => 500,
                'base_price' => 40000,
                'price_per_kg' => 200,
                'price_per_km' => 1200,
                'price_per_hour' => 30000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Yuk mashinasi (Gazelle)',
                'status' => true,
                'max_weight_kg' => 1500,
                'base_price' => 60000,
                'price_per_kg' => 150,
                'price_per_km' => 1500,
                'price_per_hour' => 40000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Katta yuk mashinasi',
                'status' => true,
                'max_weight_kg' => 5000,
                'base_price' => 100000,
                'price_per_kg' => 100,
                'price_per_km' => 2000,
                'price_per_hour' => 60000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('vehicle_types')->insert($vehicleTypes);
    }
}