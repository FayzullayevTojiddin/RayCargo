<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetListNotificationsController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $query = $user->notifications()->latest();

        if ($request->filled('status')) {
            match ($request->get('status')) {
                'read'   => $query->where('is_read', true),
                'unread' => $query->where('is_read', false),
                default  => null,
            };
        }

        $notifications = $query->paginate(
            $request->integer('per_page', 20)
        );

        return $this->response([
            'items' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }
}