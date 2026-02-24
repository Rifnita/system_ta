<?php

namespace App\Filament\Resources\KategoriTransaksiKeuangans\Pages;

use App\Filament\Resources\KategoriTransaksiKeuangans\KategoriTransaksiKeuanganResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListKategoriTransaksiKeuangans extends ListRecords
{
    protected static string $resource = KategoriTransaksiKeuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Kategori'),
        ];
    }
}
