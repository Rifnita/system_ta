<?php

namespace App\Filament\Resources\KategoriLaporanAktivitasResource\Pages;

use App\Filament\Resources\KategoriLaporanAktivitasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKategoriLaporanAktivitas extends ListRecords
{
    protected static string $resource = KategoriLaporanAktivitasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Kategori')
                ->icon('heroicon-o-plus'),
        ];
    }
}
