<?php

namespace App\Http\Controllers\Courier\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\JsonResponse;

class RegisterController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $login = $request->login;
        $loginType = $this->detectLoginType($login);

        $uniqueField = $loginType === 'email' ? 'email' : 'phone_number';
        $request->validate([
            'login' => ["unique:users,{$uniqueField}"],
        ]);

        $key = 'courier:register:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);

            return response()->json([
                'success' => false,
                'message' => __('messages.errors.too_many_attempts', ['minutes' => $minutes]),
            ], 429);
        }

        RateLimiter::hit($key, 3600);

        $verificationId = Str::random(64);
        $verificationCode = rand(100000, 999999);

        Cache::put("courier:register:verify:{$verificationId}", [
            'name' => $request->name,
            'login' => $login,
            'login_type' => $loginType,
            'password' => bcrypt($request->password),
            'code' => $verificationCode,
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
            // Hozircha SMS yuborilmaydi, kod log ga yoziladi
            logger()->info("Courier SMS verification code for {$login}: {$code}");
        }
    }
}
