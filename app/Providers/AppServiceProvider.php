<?php

namespace App\Providers;

use App\Filament\Auth\Responses\LoginResponse as FilamentLoginResponse;
use App\Models\LaporanAktivitas;
use App\Models\PengajuanCuti;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Policies\LaporanAktivitasPolicy;
use App\Policies\PengajuanCutiPolicy;
use Carbon\Carbon;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as FilamentLoginResponseContract;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        App::bind(\Filament\Auth\Notifications\VerifyEmail::class, VerifyEmailNotification::class);
        App::bind(\Filament\Auth\Notifications\ResetPassword::class, ResetPasswordNotification::class);
        $this->app->bind(FilamentLoginResponseContract::class, FilamentLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        App::setLocale('id');
        Carbon::setLocale('id');

        // Register policies
        Gate::policy(LaporanAktivitas::class, LaporanAktivitasPolicy::class);
        Gate::policy(PengajuanCuti::class, PengajuanCutiPolicy::class);
    }
}
