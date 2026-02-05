<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordResetController;

Route::get('/', function () {
    return view('welcome');
});

// Password Reset Routes
Route::get('/password/reset', [PasswordResetController::class, 'show'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');
