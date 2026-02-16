<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyRegisterController;
use App\Http\Controllers\Auth\ResendRegisterCodeController;
use Illuminate\Support\Facades\Route;

Route::middleware('language')->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::post('/login', LoginController::class);
        Route::post('/register', RegisterController::class);
        Route::post('/register/verify', VerifyRegisterController::class);
        Route::post('/register/resend', ResendRegisterCodeController::class);
    });
});