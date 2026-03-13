<?php

namespace App\Http\Controllers\Order;

use App\Enums\User\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ListOrdersController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Order::with(['vehicleType:id,title,icon', 'stops'])
            ->orderByDesc('created_at');

        if ($user->role === UserRole::COURIER) {
            $query->where('courier_id', $user->id);
        } else {
            $query->where('client_id', $user->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }
}
