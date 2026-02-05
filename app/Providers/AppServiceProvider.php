<?php

namespace App\Providers;

use Filament\Support\Colors\Color;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure Filament with Gold luxury color palette
        $this->configureFilamentTheme();
    }

    /**
     * Configure Filament theme with gold luxury palette
     */
    private function configureFilamentTheme(): void
    {
        // Gold palette colors
        $goldPalette = [
            50 => '#FFFBF0',
            100 => '#FEF3E2',
            200 => '#FDE8C9',
            300 => '#FCDCA8',
            400 => '#F9C66D',
            500 => '#D4AF37',
            600 => '#C9A227',
            700 => '#B8941F',
            800 => '#8B6914',
            900 => '#5A450C',
            950 => '#3D2E08',
        ];
    }
}
