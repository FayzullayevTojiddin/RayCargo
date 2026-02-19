<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResendRegisterCodeController;
use App\Http\Controllers\Auth\VerifyRegisterController;
use App\Http\Controllers\Order\CalculatePriceController;
use App\Http\Controllers\Order\CreateOrderController;
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
            Route::post('/calculate-price', CalculatePriceController::class);
            Route::post('/', CreateOrderController::class);
        });
    });
});
