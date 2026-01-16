<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetUnreadNotificationsCountController extends Controller
{
    public function __invoke(Request $request)
    {
        $count = $request->user()
            ->notifications()
            ->where('is_read', false)
            ->count();

        return $this->response([
            'unread_count' => $count
        ]);
    }
}