<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResendRegisterCodeController;
use App\Http\Controllers\Auth\VerifyRegisterController;
use App\Http\Controllers\Courier\ApplicationStatusController;
use App\Http\Controllers\Courier\Auth\LoginController as CourierLoginController;
use App\Http\Controllers\Courier\Auth\RegisterController as CourierRegisterController;
use App\Http\Controllers\Courier\Auth\ResendCodeController as CourierResendCodeController;
use App\Http\Controllers\Courier\Auth\VerifyController as CourierVerifyController;
use App\Http\Controllers\Courier\SubmitProfileController;
use App\Http\Controllers\Order\CalculatePriceController;
use App\Http\Controllers\Order\CreateOrderController;
use App\Http\Controllers\Order\ListOrdersController;
use App\Http\Controllers\Profile\UpdateProfileController;
use App\Http\Controllers\Profile\UpdateProfileImageController;
use Illuminate\Support\Facades\Route;

Route::middleware('language')->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::post('/login', LoginController::class);
        Route::post('/register', RegisterController::class);
        Route::post('/register/verify', VerifyRegisterController::class);
        Route::post('/register/resend', ResendRegisterCodeController::class);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('/profile')->group(function () {
            Route::put('/', UpdateProfileController::class);
            Route::post('/image', UpdateProfileImageController::class);
        });

        Route::prefix('/orders')->group(function () {
            Route::get('/', ListOrdersController::class);
            Route::post('/calculate-price', CalculatePriceController::class);
            Route::post('/', CreateOrderController::class);
        });
    });

    // Courier routes
    Route::prefix('/courier')->group(function () {
        Route::prefix('/auth')->group(function () {
            Route::post('/register', CourierRegisterController::class);
            Route::post('/login', CourierLoginController::class);
            Route::post('/verify', CourierVerifyController::class);
            Route::post('/resend', CourierResendCodeController::class);
        });

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/profile/submit', SubmitProfileController::class);
            Route::get('/application-status', ApplicationStatusController::class);
        });
    });
});
