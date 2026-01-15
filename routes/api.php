<?php

use App\Http\Controllers\Settings\SetLanguageController;
use App\Http\Controllers\Settings\SetPasswordController;
use App\Http\Controllers\User\LogoutController;
use App\Http\Controllers\User\RequestCodeController;
use App\Http\Controllers\User\VerifyCodeController;
use Illuminate\Support\Facades\Route;

Route::middleware('language')->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::post('/request-code', RequestCodeController::class);
        Route::post('/verify-code', VerifyCodeController::class);
        Route::delete('/logout', LogoutController::class)->middleware('auth:sanctum');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('/client')->group(function () {
            
        });

        Route::prefix('/driver')->group(function () {

        });

        Route::prefix('/settings')->group(function () {
            Route::put('/language', SetLanguageController::class);
        });
    });
});
