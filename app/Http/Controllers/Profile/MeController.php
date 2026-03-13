<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'image' => $user->image ? asset('uploads/' . $user->image) : null,
                'role' => $user->role,
                'status' => $user->status,
                'lang' => $user->lang,
                'last_seen_at' => $user->last_seen_at,
            ],
        ]);
    }
}
