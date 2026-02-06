<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\LaporanAktivitasExportController;

Route::get('/', function () {
    return redirect('/admin/login');
});

// Password Reset Routes
Route::get('/password/reset', [PasswordResetController::class, 'show'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/laporan-aktivitas/export/pdf', [LaporanAktivitasExportController::class, 'exportPdf'])
        ->name('laporan-aktivitas.export.pdf');

    Route::get('/laporan-aktivitas/{laporanAktivitas}/export/pdf', [LaporanAktivitasExportController::class, 'exportSinglePdf'])
        ->name('laporan-aktivitas.export.single.pdf');
});
