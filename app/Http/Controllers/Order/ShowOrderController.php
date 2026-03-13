<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ShowOrderController extends Controller
{
    public function __invoke(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($order->client_id !== $user->id && $order->courier_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ruxsat yo\'q.',
            ], 403);
        }

        $order->load(['stops', 'items', 'vehicleType:id,title,icon', 'client:id,name,phone_number', 'courier:id,name,phone_number']);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }
}
