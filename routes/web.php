<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\PasswordResetController;
use App\Notifications\VerifyEmailNotification;
use App\Http\Controllers\LaporanAktivitasExportController;
use App\Http\Controllers\LaporanMingguanExportController;
use App\Http\Controllers\TransaksiKeuanganExportController;

Route::get('/', function () {
    return redirect('/admin/login');
});

// Password Reset Routes
Route::get('/password/reset', [PasswordResetController::class, 'show'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');

// Email Verification Route
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::post('/email/verification-notification', function (Request $request) {
    if ($request->user()?->hasVerifiedEmail()) {
        return redirect('/admin');
    }

    $request->user()?->notify(new VerifyEmailNotification());

    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/laporan-aktivitas/export/pdf', [LaporanAktivitasExportController::class, 'exportPdf'])
        ->name('laporan-aktivitas.export.pdf');

    Route::get('/laporan-aktivitas/{laporanAktivitas}/export/pdf', [LaporanAktivitasExportController::class, 'exportSinglePdf'])
        ->name('laporan-aktivitas.export.single.pdf');

    Route::get('/transaksi-keuangan/export/pdf', [TransaksiKeuanganExportController::class, 'exportPdf'])
        ->name('transaksi-keuangan.export.pdf');

    Route::get('/transaksi-keuangan/export/excel', [TransaksiKeuanganExportController::class, 'exportExcel'])
        ->name('transaksi-keuangan.export.excel');
});

// Laporan Mingguan PDF Export (outside admin prefix to match route name)
Route::middleware(['auth'])->group(function () {
    Route::get('/laporan-mingguan/{laporanMingguan}/pdf', [LaporanMingguanExportController::class, 'exportPdf'])
        ->name('laporan-mingguan.pdf');
});
