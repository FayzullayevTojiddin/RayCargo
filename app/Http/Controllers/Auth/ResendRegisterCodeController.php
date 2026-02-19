<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResendRegisterCodeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'verification_id' => ['required', 'string'],
        ]);

        $key = 'resend:' . $request->verification_id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
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

        RateLimiter::hit($key, 60);

        $newCode = rand(100000, 999999);
        $data['code'] = $newCode;

        Cache::put($cacheKey, $data, now()->addMinutes(10));

        $this->sendVerificationCode($data['email'], $newCode);

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.verification_code_resent'),
        ]);
    }

    private function sendVerificationCode(string $email, int $code): void
    {
        Mail::to($email)->send(new VerificationCodeMail($code));
    }
}
