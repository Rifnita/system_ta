<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->brandName('System TA')
            ->favicon(asset('favicon.ico'))
            ->colors([
                // Gold Luxury Palette - Primary Brand Color
                'primary' => [
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
                ],
                'danger' => Color::Rose,
                'gray' => Color::Slate,
                'info' => [
                    50 => '#F0F8FF',
                    100 => '#E6F2FF',
                    200 => '#BAD9FF',
                    300 => '#7EB3FF',
                    400 => '#3B82F6',
                    500 => '#1D4ED8',
                    600 => '#1E40AF',
                    700 => '#1E3A8A',
                    800 => '#1E3A8A',
                    900 => '#0C2D6B',
                    950 => '#051E3E',
                ],
                'success' => Color::Emerald,
                'warning' => [
                    50 => '#FFF7ED',
                    100 => '#FFEDD5',
                    200 => '#FED7AA',
                    300 => '#FDBA74',
                    400 => '#FB923C',
                    500 => '#F97316',
                    600 => '#EA580C',
                    700 => '#C2410C',
                    800 => '#9A3412',
                    900 => '#7C2D12',
                    950 => '#431407',
                ],
            ])
            ->font('Inter')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->plugin(FilamentShieldPlugin::make())
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
