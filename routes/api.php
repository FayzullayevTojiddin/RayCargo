<?php

use App\Http\Controllers\User\RequestCodeController;
use App\Http\Controllers\User\VerifyCodeController;
use Illuminate\Support\Facades\Route;

Route::middleware('language')->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::post('/request-code', RequestCodeController::class);
        Route::post('/verify-code', VerifyCodeController::class);
    });
});