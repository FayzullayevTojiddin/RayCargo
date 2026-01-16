<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $client = $user->client;
        $wallet = $user->wallet;

        return $this->response([
            'client' => [
                'id'           => $client?->id,
                'is_online'    => $client?->is_online,
                'is_active'    => $client?->is_active,
                'last_seen_at' => $client?->last_seen_at,
            ],
            'user' => [
                'id'           => $user->id,
                'email'        => $user->email,
                'phone_number' => $user->phone_number,
                'image'        => $user->image,
                'status'       => $user->status,
                'language'     => $user->lang,
                'balance'      => $wallet?->balance ?? 0,
            ],
        ]);
    }
}