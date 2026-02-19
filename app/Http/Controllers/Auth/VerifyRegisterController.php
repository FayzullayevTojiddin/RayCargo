<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\JsonResponse;

class VerifyRegisterController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'verification_id' => ['required', 'string'],
            'code' => ['required', 'numeric', 'digits:6'],
        ]);

        $key = 'verify:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);

            return response()->json([
                'success' => false,
                'message' => __('messages.errors.too_many_attempts', ['minutes' => $minutes])
            ], 429);
        }

        $cacheKey = "register:verify:{$request->verification_id}";
        $data = Cache::get($cacheKey);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.verification_expired')
            ], 400);
        }

        if ($data['code'] != $request->code) {
            RateLimiter::hit($key, 3600);

            return response()->json([
                'success' => false,
                'message' => __('messages.auth.verification_code_invalid')
            ], 400);
        }

        RateLimiter::clear($key);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'password' => $data['password'],
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
            'login_type' => 'email',
            'last_login_at' => now(),
        ]);

        Cache::forget($cacheKey);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.register_success'),
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ]);
    }
}
