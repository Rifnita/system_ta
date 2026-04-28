<?php

namespace App\Filament\Auth\Pages;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    protected string $view = 'filament.auth.pages.login';

    protected Width | string | null $maxWidth = Width::ScreenTwoExtraLarge;

    public function getTitle(): string | Htmlable
    {
        return 'Masuk ke akun Anda';
    }

    public function getHeading(): string | Htmlable | null
    {
        return filled($this->userUndertakingMultiFactorAuthentication)
            ? __('filament-panels::auth/pages/login.multi_factor.heading')
            : 'Masuk ke akun Anda';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return filled($this->userUndertakingMultiFactorAuthentication)
            ? __('filament-panels::auth/pages/login.multi_factor.subheading')
            : 'Gunakan email dan password yang telah terdaftar untuk masuk ke portal pemasaran perumahan.';
    }

    protected function getPasswordFormComponent(): Component
    {
        $component = parent::getPasswordFormComponent();

        if (filament()->hasPasswordReset()) {
            $component->hint(
                new HtmlString(Blade::render(
                    '<x-filament::link :href="filament()->getRequestPasswordResetUrl()">Lupa kata sandi?</x-filament::link>'
                ))
            )->hintColor('primary');
        }

        return $component;
    }
}