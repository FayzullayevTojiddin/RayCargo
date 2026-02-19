<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\JsonResponse;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $key = 'register:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);

            return response()->json([
                'success' => false,
                'message' => __('messages.errors.too_many_attempts', ['minutes' => $minutes])
            ], 429);
        }

        RateLimiter::hit($key, 3600);

        $verificationId = Str::random(64);
        $verificationCode = rand(100000, 999999);

        Cache::put("register:verify:{$verificationId}", [
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => bcrypt($request->password),
            'code' => $verificationCode,
        ], now()->addMinutes(10));

        $this->sendVerificationCode($request->email, $verificationCode);

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.verification_code_sent'),
            'data' => [
                'verification_id' => $verificationId,
                'expires_in' => 600,
            ]
        ]);
    }

    private function sendVerificationCode(string $email, int $code): void
    {
        Mail::to($email)->send(new VerificationCodeMail($code));
    }
}
