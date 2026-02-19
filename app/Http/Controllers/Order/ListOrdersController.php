<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ListOrdersController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $orders = Order::where('client_id', $request->user()->id)
            ->with(['vehicleType', 'stops'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }
}
