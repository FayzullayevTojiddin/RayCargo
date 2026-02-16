<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Models\User;
use App\Enums\User\UserStatus;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $key = 'login:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);
            
            return response()->json([
                'success' => false,
                'message' => __('messages.errors.too_many_attempts', ['minutes' => $minutes])
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 86400);
            
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.failed')
            ], 401);
        }

        if ($user->status === UserStatus::BLOCKED) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.user_blocked')
            ], 403);
        }

        RateLimiter::clear($key);

        $user->update([
            'last_login_at' => now(),
            'fcm_token' => $request->fcm_token,
            'email_verified_at' => now(),
            'login_type' => 'email',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.login_success'),
            'data' => [
                'user' => $user->fresh(),
                'token' => $token,
            ]
        ]);
    }
}