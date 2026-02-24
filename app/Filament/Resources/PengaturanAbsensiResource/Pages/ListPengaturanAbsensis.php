<?php

namespace App\Filament\Resources\PengaturanAbsensiResource\Pages;

use App\Filament\Resources\PengaturanAbsensiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengaturanAbsensis extends ListRecords
{
    protected static string $resource = PengaturanAbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
