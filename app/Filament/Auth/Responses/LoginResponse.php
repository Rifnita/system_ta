<?php

namespace App\Filament\Auth\Responses;

use App\Filament\Resources\AbsensiResource;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        return redirect()->to(AbsensiResource::getUrl('index'));
    }
}
