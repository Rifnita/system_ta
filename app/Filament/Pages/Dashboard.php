<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dasbor';

    public function getTitle(): string | Htmlable
    {
        return 'Dasbor';
    }

    public function getHeading(): string | Htmlable
    {
        return 'Dasbor';
    }
}
