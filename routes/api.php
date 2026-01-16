<?php

use App\Http\Controllers\Client\ClientProfileController;
use App\Http\Controllers\Notification\GetListNotificationsController;
use App\Http\Controllers\Notification\GetNotificationController;
use App\Http\Controllers\Notification\GetUnreadNotificationsCountController;
use App\Http\Controllers\Settings\SetLanguageController;
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
            Route::get('/me', ClientProfileController::class);
        });

        Route::prefix('/driver')->group(function () {});

        Route::prefix('/settings')->group(function () {
            Route::put('/language', SetLanguageController::class);
        });

        Route::prefix('/notifications')->group(function () {
            Route::get('/', GetListNotificationsController::class);
            Route::get('/{notification}', GetNotificationController::class);
            Route::get('/unread-count', GetUnreadNotificationsCountController::class);
        });
    });
});