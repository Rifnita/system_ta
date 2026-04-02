<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\EditProfile;
use App\Filament\Resources\AbsensiResource;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Enums\ThemeMode;
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
            ->homeUrl(fn (): string => AbsensiResource::getUrl('index'))
            ->darkMode(false)
            ->defaultThemeMode(ThemeMode::Light)
            ->passwordReset()
            ->emailVerification()
            ->brandName(config('app.name', 'System TA'))
            ->brandLogo(function (): HtmlString {
                $brandName = e((string) config('app.name', 'System TA'));

                if (file_exists(public_path('images/company-logo.svg'))) {
                    $logoUrl = asset('images/company-logo.svg');
                } elseif (file_exists(public_path('images/company-logo.png'))) {
                    $logoUrl = asset('images/company-logo.png');
                } else {
                    return new HtmlString(
                        '<span style="font-weight:700; font-size:1rem; color:#243a66; line-height:1.1;">' . $brandName . '</span>'
                    );
                }

                return new HtmlString(
                    '<span style="display:inline-flex; align-items:center; gap:.55rem;">'
                    . '<img src="' . $logoUrl . '" alt="' . $brandName . '" style="height:2rem; width:auto; object-fit:contain;" />'
                    . '<span style="font-weight:700; font-size:1rem; color:#243a66; line-height:1.1; white-space:nowrap;">' . $brandName . '</span>'
                    . '</span>'
                );
            })
            ->brandLogoHeight('2rem')
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
                    950 => '#10214b',
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
                    950 => '#10214b',
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
                    950 => '#5a4d35',
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
                            --ui-radius: 10px;
                        }

                        .fi-simple-main .fi-simple-header-heading {
                            font-size: 1.05rem !important;
                            font-weight: 600 !important;
                            letter-spacing: 0 !important;
                            color: #111111 !important;
                        }

                        .fi-simple-main .fi-simple-header-subheading {
                            font-size: 0.875rem !important;
                            font-weight: 400 !important;
                            color: #222222 !important;
                        }

                        .fi-body {
                            background: linear-gradient(180deg, #f8f2e6 0%, #eff1f7 100%) !important;
                        }

                        .fi-page,
                        .fi-page-content,
                        .fi-main {
                            background-color: transparent !important;
                        }

                        .fi-sidebar {
                            background: linear-gradient(180deg, #fffaf2 0%, #f8f2e6 100%) !important;
                            border-inline-end: 1px solid #e5d2a6 !important;
                        }

                        .fi-sidebar .fi-sidebar-item-label,
                        .fi-sidebar .fi-sidebar-group-label {
                            color: #243a66 !important;
                        }

                        .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn,
                        .fi-sidebar .fi-sidebar-item.fi-sidebar-item-has-active-child-items > .fi-sidebar-item-btn,
                        .fi-sidebar .fi-sidebar-item > .fi-sidebar-item-btn[aria-current="page"] {
                            background: linear-gradient(90deg, #d9deea 0%, #b3bdd6 100%) !important;
                            box-shadow: 0 4px 10px rgba(16, 33, 75, 0.1) !important;
                        }

                        .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-sidebar-item-label,
                        .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-icon,
                        .fi-sidebar .fi-sidebar-item.fi-sidebar-item-has-active-child-items > .fi-sidebar-item-btn > .fi-sidebar-item-label,
                        .fi-sidebar .fi-sidebar-item.fi-sidebar-item-has-active-child-items > .fi-sidebar-item-btn > .fi-icon,
                        .fi-sidebar .fi-sidebar-item > .fi-sidebar-item-btn[aria-current="page"] > .fi-sidebar-item-label,
                        .fi-sidebar .fi-sidebar-item > .fi-sidebar-item-btn[aria-current="page"] > .fi-icon {
                            color: #192a4d !important;
                        }

                        .fi-sidebar .fi-sidebar-item-btn,
                        .fi-sidebar .fi-sidebar-group-dropdown-trigger-btn {
                            border-radius: var(--ui-radius) !important;
                        }

                        .fi-sidebar .fi-sidebar-item.fi-sidebar-item-has-url > .fi-sidebar-item-btn:hover,
                        .fi-sidebar .fi-sidebar-item.fi-sidebar-item-has-url > .fi-sidebar-item-btn:focus-visible {
                            background-color: rgba(217, 189, 136, 0.3) !important;
                        }

                        .fi-sidebar .fi-sidebar-item.fi-sidebar-item-has-url > .fi-sidebar-item-btn:hover > .fi-sidebar-item-label,
                        .fi-sidebar .fi-sidebar-item.fi-sidebar-item-has-url > .fi-sidebar-item-btn:hover > .fi-icon {
                            color: #243a66 !important;
                        }

                        .fi-topbar {
                            background: linear-gradient(90deg, #fffaf2 0%, #f8f2e6 100%) !important;
                            border-bottom: 1px solid #e5d2a6 !important;
                        }

                        .fi-topbar .fi-topbar-item-btn > .fi-topbar-item-label,
                        .fi-topbar .fi-topbar-item-btn > .fi-icon,
                        .fi-topbar .fi-topbar-open-sidebar-btn > .fi-icon,
                        .fi-topbar .fi-topbar-close-sidebar-btn > .fi-icon {
                            color: #243a66 !important;
                        }

                        .fi-topbar .fi-input-wrp {
                            background-color: #f8f2e6 !important;
                            border-color: #d9bf7f !important;
                            border-radius: var(--ui-radius) !important;
                        }

                        .fi-topbar .fi-input-wrp input {
                            color: #243a66 !important;
                        }

                        .fi-topbar .fi-input-wrp input::placeholder {
                            color: #667cac !important;
                        }

                        .fi-topbar .fi-dropdown-panel,
                        .fi-topbar .fi-dropdown-list,
                        .fi-topbar .fi-dropdown-list-item,
                        .fi-topbar .fi-dropdown-list-item-label,
                        .fi-topbar .fi-dropdown-header,
                        .fi-topbar .fi-dropdown-panel .fi-icon {
                            background-color: #fffaf2 !important;
                            color: #243a66 !important;
                        }

                        .fi-topbar .fi-dropdown-panel,
                        .fi-topbar .fi-user-menu-trigger,
                        .fi-topbar .fi-topbar-item-btn {
                            border-radius: var(--ui-radius) !important;
                        }

                        .fi-topbar .fi-user-menu-trigger .fi-avatar,
                        .fi-topbar .fi-user-menu-trigger img {
                            border: 2px solid #d9bf7f !important;
                            border-radius: 9999px !important;
                            box-shadow: 0 0 0 2px rgba(16, 33, 75, 0.45) !important;
                            background-color: #fffaf2 !important;
                        }

                        .fi-btn,
                        .fi-badge {
                            border-radius: var(--ui-radius) !important;
                        }

                        .fi-btn.fi-color-primary {
                            background: linear-gradient(90deg, #405b97 0%, #2f497f 100%) !important;
                            color: #f8f2e6 !important;
                            border: 1px solid #243a66 !important;
                            box-shadow: 0 6px 14px rgba(64, 91, 151, 0.2) !important;
                        }

                        .fi-btn.fi-color-primary:hover {
                            background: linear-gradient(90deg, #2f497f 0%, #243a66 100%) !important;
                        }

                        .fi-wi {
                            gap: 1rem !important;
                        }

                        .fi-dashboard .fi-wi-widget,
                        .fi-dashboard .fi-wi-stats-overview {
                            background: transparent !important;
                            border: 0 !important;
                            box-shadow: none !important;
                            padding: 0 !important;
                        }

                        .fi-wi-stats-overview .fi-section {
                            background: transparent !important;
                            border: 0 !important;
                            box-shadow: none !important;
                            outline: 0 !important;
                            padding: 0 !important;
                        }

                        .fi-wi-stats-overview .fi-section-content-ctn,
                        .fi-wi-stats-overview .fi-section-content {
                            background: transparent !important;
                            border: 0 !important;
                            box-shadow: none !important;
                            outline: 0 !important;
                            padding: 0 !important;
                            margin: 0 !important;
                        }

                        .fi-wi-stats-overview.fi-section,
                        .fi-wi-stats-overview {
                            background: transparent !important;
                            border: 0 !important;
                            box-shadow: none !important;
                            outline: 0 !important;
                            padding: 0 !important;
                        }

                        .fi-section:not(.fi-wi-stats-overview) {
                            background: #fffaf2 !important;
                            border: 1px solid #e5d2a6 !important;
                            border-radius: var(--ui-radius) !important;
                            transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease !important;
                        }

                        .fi-wi-stats-overview-stat {
                            background: #fffaf2 !important;
                            border: 1px solid #e5d2a6 !important;
                            border-radius: var(--ui-radius) !important;
                            box-shadow: 0 2px 6px rgba(16, 33, 75, 0.06) !important;
                            padding: 1rem !important;
                            transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease !important;
                        }

                        .fi-section:not(.fi-wi-stats-overview):hover,
                        .fi-wi-stats-overview-stat:hover {
                            transform: translateY(-1px) !important;
                            border-color: #d0ad63 !important;
                            box-shadow: 0 8px 16px rgba(16, 33, 75, 0.1) !important;
                        }

                        .fi-wi-stats-overview-stat .fi-wi-stats-overview-stat-label {
                            color: #405b97 !important;
                            font-weight: 600 !important;
                        }

                        .fi-wi-stats-overview-stat .fi-wi-stats-overview-stat-value {
                            color: #10214b !important;
                        }

                        .fi-wi-stats-overview-stat .fi-wi-stats-overview-stat-description {
                            color: #243a66 !important;
                        }

                        .fi-sidebar-nav {
                            scrollbar-width: none !important;
                            -ms-overflow-style: none !important;
                        }

                        .fi-sidebar-nav::-webkit-scrollbar {
                            width: 0 !important;
                            height: 0 !important;
                        }

                        .fi-wi-chart canvas {
                            transition: transform 180ms ease !important;
                        }

                        .fi-wi-widget:hover .fi-wi-chart canvas {
                            transform: scale(1.005) !important;
                        }

                        .fi-ta,
                        .fi-ta-ctn {
                            background: #fffaf2 !important;
                            border: 1px solid #e5d2a6 !important;
                            border-radius: var(--ui-radius) !important;
                        }

                        .fi-ta-table thead tr {
                            background: #d9deea !important;
                        }

                        .fi-ta-table thead th {
                            color: #192a4d !important;
                            border-bottom: 1px solid #b3bdd6 !important;
                        }

                        .fi-ta-table tbody td {
                            color: #243a66 !important;
                        }

                        .fi-ta-table tbody tr,
                        .fi-ta-table tbody tr:hover {
                            background-color: transparent !important;
                            transform: none !important;
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
                    ->visible(fn (): bool => EditProfile::canAccess())
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
