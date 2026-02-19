<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CalculatePriceRequest;
use App\Services\Order\PriceCalculatorService;
use Symfony\Component\HttpFoundation\JsonResponse;

class CalculatePriceController extends Controller
{
    public function __construct(
        private PriceCalculatorService $priceCalculator
    ) {}

    public function __invoke(CalculatePriceRequest $request): JsonResponse
    {
        $result = $this->priceCalculator->calculateForAllVehicleTypes(
            $request->validated()['stops']
        );

        return response()->json([
            'success' => true,
            'message' => __('messages.order.price_calculated'),
            'data' => $result,
        ]);
    }
}
