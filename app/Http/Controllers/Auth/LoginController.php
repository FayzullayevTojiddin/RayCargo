<?php

namespace App\Http\Controllers\Auth;

use App\Enums\User\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request)
    {
        $key = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.errors.too_many_attempts'),
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);

            return response()->json([
                'success' => false,
                'message' => __('messages.auth.failed'),
            ], 401);
        }

        if ($user->status === UserStatus::BLOCKED) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.user_blocked'),
            ], 403);
        }

        RateLimiter::clear($key);

        $user->update([
            'login_type' => 'email',
            'last_login_at' => now(),
            'email_verified_at' => $user->email_verified_at ?? now(),
            'fcm_token' => $request->fcm_token ?? $user->fcm_token,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.login_success'),
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 200);
    }
}
