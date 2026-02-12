<?php

namespace Database\Seeders;

use App\Enums\Order\OrderPriceItemType;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderPriceItemSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::all();

        if ($orders->isEmpty()) {
            return;
        }

        $priceItems = [];
        
        foreach ($orders as $order) {
            $itemCount = rand(2, 5);
            
            for ($i = 0; $i < $itemCount; $i++) {
                $type = OrderPriceItemType::cases()[array_rand(OrderPriceItemType::cases())];
                
                $priceItems[] = [
                    'order_id' => $order->id,
                    'type' => $type->value,
                    'amount' => rand(5000, 100000),
                    'meta' => json_encode($this->getMetaForType($type)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('order_price_items')->insert($priceItems);
    }

    private function getMetaForType(OrderPriceItemType $type): array
    {
        return match($type) {
            OrderPriceItemType::BASE => [
                'description' => "Asosiy to'lov",
                'vehicle_type' => 'Yengil avtomobil',
            ],
            OrderPriceItemType::DELIVERY => [
                'distance_km' => rand(5, 500) / 10,
                'price_per_km' => rand(1000, 3000),
                'description' => 'Yetkazib berish',
            ],
            OrderPriceItemType::SERVICE => [
                'service_name' => ["Yukni ko'tarish", 'Qadoqlash', 'Kutish vaqti'][array_rand(["Yukni ko'tarish", 'Qadoqlash', 'Kutish vaqti'])],
                'description' => "Qo'shimcha xizmat",
            ],
            OrderPriceItemType::DISCOUNT => [
                'discount_percent' => rand(5, 30),
                'promo_code' => 'PROMO' . rand(100, 999),
                'description' => 'Chegirma',
            ],
            OrderPriceItemType::BONUS => [
                'bonus_points' => rand(100, 1000),
                'description' => 'Bonus ball',
            ],
            OrderPriceItemType::TAX => [
                'tax_percent' => 15,
                'description' => 'QQS',
            ],
        };
    }
}