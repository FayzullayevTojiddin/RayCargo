<?php

namespace App\Services\Order;

class DistanceCalculatorService
{
    public function calculate(
        float $fromLat,
        float $fromLng,
        float $toLat,
        float $toLng
    ): float {
        $earthRadius = 6371;

        $fromLat = deg2rad($fromLat);
        $fromLng = deg2rad($fromLng);
        $toLat   = deg2rad($toLat);
        $toLng   = deg2rad($toLng);

        $latDelta = $toLat - $fromLat;
        $lngDelta = $toLng - $fromLng;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($fromLat) * cos($toLat) *
            pow(sin($lngDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }
}