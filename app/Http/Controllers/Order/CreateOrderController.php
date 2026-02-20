<?php

namespace App\Http\Controllers\Order;

use App\Enums\Order\OrderPriceItemType;
use App\Enums\Order\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Models\Order;
use App\Models\VehicleType;
use App\Services\Order\PriceCalculatorService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreateOrderController extends Controller
{
    public function __construct(
        private PriceCalculatorService $priceCalculator
    ) {}

    public function __invoke(CreateOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $stops = $validated['stops'];

        $vehicleType = VehicleType::where('id', $validated['vehicle_type_id'])
            ->where('is_active', true)
            ->first();

        if (!$vehicleType) {
            return response()->json([
                'success' => false,
                'message' => __('messages.order.vehicle_type_not_available'),
            ], 422);
        }

        $totalDistance = $this->priceCalculator->calculateTotalDistance($stops);
        $totalPrice = $this->priceCalculator->calculatePrice($vehicleType, $totalDistance);
        $breakdown = $this->priceCalculator->calculateBreakdown($vehicleType, $totalDistance);

        $order = DB::transaction(function () use ($request, $validated, $vehicleType, $stops, $totalDistance, $totalPrice, $breakdown) {
            $order = Order::create([
                'client_id' => $request->user()->id,
                'vehicle_type_id' => $vehicleType->id,
                'status' => OrderStatus::CREATED,
                'total_distance_km' => $totalDistance,
                'total_price' => $totalPrice,
            ]);

            // Stoplarni saqlash
            foreach ($stops as $index => $stop) {
                $order->stops()->create([
                    'type' => $stop['type'],
                    'sequence' => $index + 1,
                    'lat' => $stop['lat'],
                    'lng' => $stop['lng'],
                    'address_text' => $stop['address_text'] ?? null,
                ]);
            }

            // Narx tarkibini saqlash
            $order->items()->create([
                'type' => OrderPriceItemType::BASE,
                'amount' => $breakdown['base_price'],
                'meta' => ['description' => 'Bazaviy narx'],
            ]);

            $order->items()->create([
                'type' => OrderPriceItemType::DELIVERY,
                'amount' => $breakdown['delivery_price'],
                'meta' => [
                    'distance_km' => $breakdown['distance_km'],
                    'price_per_km' => $breakdown['price_per_km'],
                ],
            ]);

            return $order;
        });

        $order->load(['stops', 'items', 'vehicleType:id,icon']);
        $order->setHidden(['vehicle_type_id']);

        return response()->json([
            'success' => true,
            'message' => __('messages.order.created'),
            'data' => [
                'order' => $order,
            ]
        ], 201);
    }
}
