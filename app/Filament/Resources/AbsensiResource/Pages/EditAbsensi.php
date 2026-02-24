<?php

namespace App\Filament\Resources\AbsensiResource\Pages;

use App\Filament\Resources\AbsensiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAbsensi extends EditRecord
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Prevent manipulation of critical fields
        // Only keterangan and status can be updated by admin
        unset($data['jam_masuk']);
        unset($data['jam_keluar']);
        unset($data['tanggal']);
        unset($data['latitude_masuk']);
        unset($data['longitude_masuk']);
        unset($data['foto_masuk']);
        unset($data['foto_keluar']);
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
