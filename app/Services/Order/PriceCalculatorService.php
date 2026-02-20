<?php

namespace App\Services\Order;

use App\Models\VehicleType;

class PriceCalculatorService
{
    public function __construct(
        private DistanceCalculatorService $distanceCalculator
    ) {}

    /**
     * Nuqtalar orasidagi umumiy masofani hisoblash (km)
     *
     * @param array $stops [['lat' => float, 'lng' => float], ...]
     */
    public function calculateTotalDistance(array $stops): float
    {
        $totalDistance = 0;

        for ($i = 0; $i < count($stops) - 1; $i++) {
            $totalDistance += $this->distanceCalculator->calculate(
                $stops[$i]['lat'],
                $stops[$i]['lng'],
                $stops[$i + 1]['lat'],
                $stops[$i + 1]['lng']
            );
        }

        return round($totalDistance, 2);
    }

    /**
     * Barcha aktiv transport turlarini narxlari bilan hisoblash
     *
     * @param array $stops [['lat' => float, 'lng' => float], ...]
     * @return array ['total_distance_km' => float, 'vehicle_types' => [...]]
     */
    public function calculateForAllVehicleTypes(array $stops): array
    {
        $totalDistance = $this->calculateTotalDistance($stops);

        $vehicleTypes = VehicleType::where('is_active', true)
            ->orderBy('max_weight_kg')
            ->get();

        $result = [];

        foreach ($vehicleTypes as $vehicleType) {
            $result[] = [
                'id' => $vehicleType->id,
                'title' => $vehicleType->title,
                'icon' => $vehicleType->icon,
                'max_weight_kg' => $vehicleType->max_weight_kg,
                'price_breakdown' => $this->calculateBreakdown($vehicleType, $totalDistance),
                'total_price' => $this->calculatePrice($vehicleType, $totalDistance),
            ];
        }

        return [
            'total_distance_km' => $totalDistance,
            'stops_count' => count($stops),
            'vehicle_types' => $result,
        ];
    }

    /**
     * Bitta transport turi uchun narx hisoblash
     */
    public function calculatePrice(VehicleType $vehicleType, float $distanceKm): int
    {
        $basePrice = (int) $vehicleType->base_price;
        $deliveryPrice = (int) round($vehicleType->price_per_km * $distanceKm);

        return $basePrice + $deliveryPrice;
    }

    /**
     * Narx tarkibini qaytarish
     */
    public function calculateBreakdown(VehicleType $vehicleType, float $distanceKm): array
    {
        $basePrice = (int) $vehicleType->base_price;
        $deliveryPrice = (int) round($vehicleType->price_per_km * $distanceKm);

        return [
            'base_price' => $basePrice,
            'delivery_price' => $deliveryPrice,
            'price_per_km' => (int) $vehicleType->price_per_km,
            'distance_km' => $distanceKm,
        ];
    }
}
