<?php

namespace App\Http\Controllers\Courier\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResendCodeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'verification_id' => ['required', 'string'],
            'type' => ['required', 'in:register,login'],
        ]);

        $key = 'courier:resend:' . $request->verification_id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
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

        RateLimiter::hit($key, 60);

        $newCode = rand(100000, 999999);
        $data['code'] = $newCode;

        Cache::put($cacheKey, $data, now()->addMinutes(10));

        $login = $data['login'] ?? null;
        $loginType = $data['login_type'];

        if ($login) {
            $this->sendVerificationCode($login, $loginType, $newCode);
        } else {
            // login type bo'lsa (user_id orqali), userni topamiz
            $user = \App\Models\User::find($data['user_id']);
            if ($user) {
                $target = $loginType === 'email' ? $user->email : $user->phone_number;
                $this->sendVerificationCode($target, $loginType, $newCode);
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.verification_code_resent'),
        ]);
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
