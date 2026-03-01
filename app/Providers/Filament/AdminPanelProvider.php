<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\EditProfile;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\HtmlString;
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
                'primary' => [
                    50 => '#eff1f7',
                    100 => '#d9deea',
                    200 => '#b3bdd6',
                    300 => '#8c9dc1',
                    400 => '#667cac',
                    500 => '#405b97',
                    600 => '#2f497f',
                    700 => '#243a66',
                    800 => '#192a4d',
                    900 => '#10214b',
                ],
                'danger' => Color::Rose,
                'gray' => Color::Slate,
                'info' => [
                    50 => '#eff1f7',
                    100 => '#d9deea',
                    200 => '#b3bdd6',
                    300 => '#8c9dc1',
                    400 => '#667cac',
                    500 => '#405b97',
                    600 => '#2f497f',
                    700 => '#243a66',
                    800 => '#192a4d',
                    900 => '#10214b',
                ],
                'success' => Color::Emerald,
                'warning' => [
                    50 => '#f8f2e6',
                    100 => '#f1e5cc',
                    200 => '#e5d2a6',
                    300 => '#d9bf7f',
                    400 => '#d0ad63',
                    500 => '#d7bd88',
                    600 => '#bfa56f',
                    700 => '#9f875a',
                    800 => '#7b6a47',
                    900 => '#5a4d35',
                ],
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(<<<'HTML'
                    <style>
                        :root {
                            --color-primary-50: #eff1f7;
                            --color-primary-100: #d9deea;
                            --color-primary-200: #b3bdd6;
                            --color-primary-300: #8c9dc1;
                            --color-primary-400: #667cac;
                            --color-primary-500: #405b97;
                            --color-primary-600: #2f497f;
                            --color-primary-700: #243a66;
                            --color-primary-800: #192a4d;
                            --color-primary-900: #10214b;
                            --color-secondary-50: #f8f2e6;
                            --color-secondary-100: #f1e5cc;
                            --color-secondary-200: #e5d2a6;
                            --color-secondary-300: #d9bf7f;
                            --color-secondary-400: #d0ad63;
                            --color-secondary-500: #d7bd88;
                            --color-secondary-600: #bfa56f;
                            --color-secondary-700: #9f875a;
                            --color-secondary-800: #7b6a47;
                            --color-secondary-900: #5a4d35;
                        }

                        .fi-dashboard .fi-wi-widget,
                        .fi-dashboard .fi-section,
                        .fi-dashboard .fi-wi-stats-overview-stat {
                            transition: transform 180ms ease, box-shadow 220ms ease, border-color 220ms ease, background-color 220ms ease;
                        }

                        .fi-dashboard .fi-wi-widget:hover,
                        .fi-dashboard .fi-section:hover,
                        .fi-dashboard .fi-wi-stats-overview-stat:hover {
                            transform: translateY(-3px);
                            border-color: var(--color-secondary-400);
                            box-shadow: 0 14px 30px rgba(16, 33, 75, 0.12);
                            background: linear-gradient(180deg, #ffffff 0%, var(--color-primary-50) 100%);
                        }

                        .fi-dashboard .fi-ta-table tbody tr {
                            transition: background-color 160ms ease;
                        }

                        .fi-dashboard .fi-ta-table tbody tr:hover {
                            background-color: #eef2fb;
                        }
                    </style>
                HTML),
            )
            ->font('Inter')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                EditProfile::class,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('Ubah Profil')
                    ->url(fn (): string => EditProfile::getUrl())
                    ->icon('heroicon-o-user-circle'),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
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
