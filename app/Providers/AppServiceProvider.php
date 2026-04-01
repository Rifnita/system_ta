<?php

namespace App\Providers;

use App\Models\LaporanAktivitas;
use App\Notifications\VerifyEmailNotification;
use App\Policies\LaporanAktivitasPolicy;
use Carbon\Carbon;
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
    }
}
