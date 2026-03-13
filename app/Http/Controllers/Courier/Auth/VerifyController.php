<?php

namespace App\Http\Controllers\Courier\Auth;

use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\JsonResponse;

class VerifyController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'verification_id' => ['required', 'string'],
            'code' => ['required', 'numeric', 'digits:6'],
            'type' => ['required', 'in:register,login'],
        ]);

        $key = 'courier:verify:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);

            return response()->json([
                'success' => false,
                'message' => __('messages.errors.too_many_attempts', ['minutes' => $minutes]),
            ], 429);
        }

        $cacheKey = "courier:{$request->type}:verify:{$request->verification_id}";
        $data = Cache::get($cacheKey);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.verification_expired'),
            ], 400);
        }

        if ($data['code'] != $request->code) {
            RateLimiter::hit($key, 3600);

            return response()->json([
                'success' => false,
                'message' => __('messages.auth.verification_code_invalid'),
            ], 400);
        }

        RateLimiter::clear($key);
        Cache::forget($cacheKey);

        if ($request->type === 'register') {
            $user = $this->handleRegister($data);
        } else {
            $user = $this->handleLogin($data);
        }

        $token = $user->createToken('courier_token')->plainTextToken;

        $courierProfile = $user->courierProfile;

        return response()->json([
            'success' => true,
            'message' => $request->type === 'register'
                ? __('messages.auth.register_success')
                : __('messages.auth.login_success'),
            'data' => [
                'user' => $user,
                'token' => $token,
                'status' => [
                    'is_active' => $courierProfile ? $courierProfile->is_active : false,
                    'is_online' => $courierProfile ? $courierProfile->is_online : false,
                    'has_profile' => $courierProfile !== null,
                ],
            ],
        ]);
    }

    private function handleRegister(array $data): User
    {
        $isEmail = $data['login_type'] === 'email';

        return User::create([
            'name' => $data['name'] ?? ($isEmail ? explode('@', $data['login'])[0] : $data['login']),
            'email' => $isEmail ? $data['login'] : null,
            'phone_number' => !$isEmail ? $data['login'] : null,
            'password' => $data['password'],
            'role' => UserRole::COURIER,
            'status' => UserStatus::ACTIVE,
            'login_type' => $isEmail ? 'email' : 'phone',
            'last_login_at' => now(),
            'email_verified_at' => $isEmail ? now() : null,
            'phone_verified_at' => !$isEmail ? now() : null,
        ]);
    }

    private function handleLogin(array $data): User
    {
        $user = User::findOrFail($data['user_id']);

        $updates = [
            'login_type' => $data['login_type'],
            'last_login_at' => now(),
        ];

        if ($data['fcm_token']) {
            $updates['fcm_token'] = $data['fcm_token'];
        }

        if ($data['login_type'] === 'email') {
            $updates['email_verified_at'] = $user->email_verified_at ?? now();
        } else {
            $updates['phone_verified_at'] = $user->phone_verified_at ?? now();
        }

        $user->update($updates);

        return $user;
    }
}
