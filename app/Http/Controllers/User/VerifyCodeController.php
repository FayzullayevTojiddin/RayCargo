<?php

namespace App\Http\Controllers\User;

use App\Enums\User\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\User\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

class VerifyCodeController extends Controller
{
    public function __invoke(Request $request)
    {
        $ipKey = 'verify-code-ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            $seconds = RateLimiter::availableIn($ipKey);
            
            throw ValidationException::withMessages([
                'code' => [
                    __('index.errors.ip_verify_limit', [
                        'minutes' => ceil($seconds / 60)
                    ])
                ],
            ]);
        }

        if (cache()->has('blocked-ip:' . $request->ip())) {
            throw ValidationException::withMessages([
                'code' => [__('index.errors.ip_blocked')],
            ]);
        }

        $data = Validator::make($request->all(), [
            'type'  => ['required', 'in:phone,email'],
            'value' => ['required', 'string'],
            'code'  => ['required', 'digits:6'],
        ])->after(function ($validator) use ($request) {

            if ($request->type === 'phone') {
                $phone = preg_replace('/\s+/', '', $request->value);
                $request->merge(['value' => $phone]);
                
                if (!$this->isValidPhone($phone)) {
                    $validator->errors()->add('value', __('index.errors.phone_invalid'));
                }
            }

            if ($request->type === 'email' && !$this->isValidEmail($request->value)) {
                $validator->errors()->add('value', __('index.errors.email_invalid'));
            }

        })->validate();

        $valueKey = 'verify-attempts:' . $data['value'];
        $attempts = cache()->get($valueKey, 0);

        if ($attempts >= 5) {
            cache()->put('blocked-value:' . $data['value'], true, now()->addMinutes(30));
            
            throw ValidationException::withMessages([
                'code' => [__('index.errors.value_verify_blocked')],
            ]);
        }

        if (cache()->has('blocked-value:' . $data['value'])) {
            throw ValidationException::withMessages([
                'code' => [__('index.errors.value_blocked')],
            ]);
        }

        $cacheKey = $this->cacheKey($data['type'], $data['value']);
        $cachedCode = cache()->get($cacheKey);

        if (!$cachedCode) {
            return response()->json([
                'message' => __('index.auth.code_expired'),
            ], 422);
        }

        if ((string) $cachedCode !== (string) $data['code']) {
            cache()->put($valueKey, $attempts + 1, now()->addMinutes(5));
            
            RateLimiter::hit($ipKey, 300);
            
            $suspiciousKey = 'suspicious-verify:' . $request->ip();
            $suspiciousCount = cache()->get($suspiciousKey, 0);
            cache()->put($suspiciousKey, $suspiciousCount + 1, now()->addMinutes(10));
            
            if ($suspiciousCount >= 15) {
                cache()->put('blocked-ip:' . $request->ip(), true, now()->addHours(24));
            }

            Log::warning('Invalid verification code attempt', [
                'ip' => $request->ip(),
                'type' => $data['type'],
                'value' => $data['value'],
                'attempts' => $attempts + 1,
                'user_agent' => $request->userAgent(),
            ]);

            $remainingAttempts = 5 - ($attempts + 1);

            return response()->json([
                'message' => __('index.auth.code_invalid'),
                'remaining_attempts' => max(0, $remainingAttempts),
            ], 422);
        }

        $user = $this->findOrCreateUser($data['type'], $data['value']);

        if ($user->status === UserStatus::BLOCKED) {
            cache()->forget($cacheKey);
            cache()->forget($valueKey);
            
            Log::warning('Blocked user tried to login', [
                'user_id' => $user->id,
                'type' => $data['type'],
                'value' => $data['value'],
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => __('index.auth.user_blocked'),
            ], 403);
        }

        if ($user->status === UserStatus::INACTIVE) {
            $user->update(['status' => UserStatus::ACTIVE]);
            
            Log::info('User activated', [
                'user_id' => $user->id,
                'type' => $data['type'],
            ]);
        }

        if ($data['type'] === 'phone') {
            $user->update([
                'phone_verified_at' => now(),
                'last_login_at' => now(),
            ]);
        } else {
            $user->update([
                'email_verified_at' => now(),
                'last_login_at' => now(),
            ]);
        }

        cache()->forget($cacheKey);
        cache()->forget($valueKey);
        
        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('User logged in successfully', [
            'user_id' => $user->id,
            'type' => $data['type'],
            'value' => $data['value'],
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => __('index.auth.login_success'),
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
                'status' => $user->status,
                'email_verified' => !is_null($user->email_verified_at),
                'phone_verified' => !is_null($user->phone_verified_at),
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    private function findOrCreateUser(string $type, string $value): User
    {
        if ($type === 'phone') {
            $phone = preg_replace('/[^\d+]/', '', $value);
            if (!str_starts_with($phone, '+') && str_starts_with($phone, '998')) {
                $phone = '+' . $phone;
            }

            $user = User::where('phone_number', $phone)->first();

            if (!$user) {
                $user = User::create([
                    'phone_number' => $phone,
                    'login_type' => 'phone',
                    'status' => UserStatus::ACTIVE,
                    'role' => UserRole::CLIENT,
                    'phone_verified_at' => now(),
                ]);

                Log::info('New user created via phone', [
                    'user_id' => $user->id,
                    'phone' => $phone,
                ]);
            }

            return $user;
        }

        $user = User::where('email', $value)->first();

        if (!$user) {
            $user = User::create([
                'email' => $value,
                'login_type' => 'email',
                'status' => UserStatus::ACTIVE,
                'role' => UserRole::CLIENT,
                'email_verified_at' => now(),
            ]);

            Log::info('New user created via email', [
                'user_id' => $user->id,
                'email' => $value,
            ]);
        }

        return $user;
    }

    private function isValidPhone(string $phone): bool
    {
        $phone = preg_replace('/[^\d+]/', '', $phone);
        
        if (!str_starts_with($phone, '+') && str_starts_with($phone, '998')) {
            $phone = '+' . $phone;
        }
        
        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $phoneNumber = $phoneUtil->parse($phone, null);
            
            return $phoneUtil->isValidNumber($phoneNumber);
        } catch (NumberParseException $e) {
            return false;
        }
    }

    private function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function cacheKey(string $type, string $value): string
    {
        return "verification_code:{$type}:{$value}";
    }
}