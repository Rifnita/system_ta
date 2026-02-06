<?php

namespace App\Filament\Resources\KategoriTaskResource\Pages;

use App\Filament\Resources\KategoriTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKategoriTask extends ListRecords
{
    protected static string $resource = KategoriTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Kategori')
                ->icon('heroicon-o-plus'),
        ];
    }
}
