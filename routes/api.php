<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardStatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/me', [AuthController::class, 'updateMe']);
        Route::put('/password', [AuthController::class, 'updatePassword']);
    });
});

Route::middleware('auth:sanctum')->prefix('admin')->group(function (): void {
    Route::get('/dashboard/stats', [DashboardStatsController::class, 'index']);
});
