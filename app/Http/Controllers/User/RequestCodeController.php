<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

class RequestCodeController extends Controller
{
    public function __invoke(Request $request)
    {
        $ipKey = 'request-code-ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            $seconds = RateLimiter::availableIn($ipKey);
            
            throw ValidationException::withMessages([
                'value' => [
                    __('index.errors.ip_rate_limit', [
                        'minutes' => ceil($seconds / 60)
                    ])
                ],
            ]);
        }

        $valueKey = 'request-code-value:' . $request->input('value');
        if (RateLimiter::tooManyAttempts($valueKey, 3)) {
            $seconds = RateLimiter::availableIn($valueKey);
            
            throw ValidationException::withMessages([
                'value' => [
                    __('index.errors.value_rate_limit', [
                        'minutes' => ceil($seconds / 60)
                    ])
                ],
            ]);
        }

        $combinedKey = 'request-code-combined:' . $request->ip() . ':' . $request->input('value');
        if (RateLimiter::tooManyAttempts($combinedKey, 2)) {
            $seconds = RateLimiter::availableIn($combinedKey);
            
            throw ValidationException::withMessages([
                'value' => [
                    __('index.errors.too_many_attempts', [
                        'minutes' => ceil($seconds / 60)
                    ])
                ],
            ]);
        }

        $suspiciousKey = 'suspicious-activity:' . $request->ip();
        $suspiciousCount = cache()->get($suspiciousKey, 0);
        
        if ($suspiciousCount >= 20) {
            cache()->put('blocked-ip:' . $request->ip(), true, now()->addHours(24));
            
            throw ValidationException::withMessages([
                'value' => [__('index.errors.suspicious_activity_blocked')],
            ]);
        }

        // Bloklangan IP ni tekshirish
        if (cache()->has('blocked-ip:' . $request->ip())) {
            throw ValidationException::withMessages([
                'value' => [__('index.errors.ip_blocked')],
            ]);
        }

        $data = Validator::make($request->all(), [
            'type'  => ['required', 'in:phone,email'],
            'value' => ['required', 'string'],
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

        RateLimiter::hit($ipKey, 3600); // 1 soat
        RateLimiter::hit($valueKey, 300); // 5 daqiqa
        RateLimiter::hit($combinedKey, 300); // 5 daqiqa

        cache()->put($suspiciousKey, $suspiciousCount + 1, now()->addHour());

        $code = random_int(100000, 999999);

        if ($data['type'] === 'phone') {
            $this->sendToPhone($data['value'], $code);
        } else {
            $this->sendToEmail($data['value'], $code);
        }

        cache()->put(
            $this->cacheKey($data['type'], $data['value']),
            $code,
            now()->addMinutes(5)
        );

        Log::info('Verification code sent', [
            'ip' => $request->ip(),
            'type' => $data['type'],
            'value' => $data['value'],
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => __('index.auth.code_sent'),
            'code' => $code,
        ]);
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

    private function sendToPhone(string $phone, int $code): void
    {
        // SMS jo'natish
    }

    private function sendToEmail(string $email, int $code): void
    {
        Mail::raw(
            __('index.auth.email_text', ['code' => $code]),
            fn ($message) => $message
                ->to($email)
                ->subject(__('index.auth.email_subject'))
        );
    }

    private function cacheKey(string $type, string $value): string
    {
        return "verification_code:{$type}:{$value}";
    }
}