<?php

namespace App\Http\Controllers\Courier\Auth;

use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\JsonResponse;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = $request->login;
        $loginType = $this->detectLoginType($login);

        $key = 'courier:login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.errors.too_many_attempts'),
            ], 429);
        }

        $field = $loginType === 'email' ? 'email' : 'phone_number';
        $user = User::where($field, $login)
            ->where('role', UserRole::COURIER)
            ->first();

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

        $verificationId = Str::random(64);
        $verificationCode = rand(100000, 999999);

        Cache::put("courier:login:verify:{$verificationId}", [
            'user_id' => $user->id,
            'login_type' => $loginType,
            'code' => $verificationCode,
            'fcm_token' => $request->fcm_token,
        ], now()->addMinutes(10));

        $this->sendVerificationCode($login, $loginType, $verificationCode);

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.verification_code_sent'),
            'data' => [
                'verification_id' => $verificationId,
                'login_type' => $loginType,
                'expires_in' => 600,
            ],
        ]);
    }

    private function detectLoginType(string $login): string
    {
        return filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
    }

    private function sendVerificationCode(string $login, string $loginType, int $code): void
    {
        if ($loginType === 'email') {
            Mail::to($login)->send(new VerificationCodeMail($code));
        } else {
            // TODO: SMS integration
            logger()->info("Courier SMS verification code for {$login}: {$code}");
        }
    }
}
