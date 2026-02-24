<?php

namespace App\Filament\Resources\PengaturanAbsensiResource\Pages;

use App\Filament\Resources\PengaturanAbsensiResource;
use App\Models\PengaturanAbsensi;
use Filament\Resources\Pages\CreateRecord;

class CreatePengaturanAbsensi extends CreateRecord
{
    protected static string $resource = PengaturanAbsensiResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Jika pengaturan baru diset aktif, nonaktifkan yang lain
        if ($data['aktif'] ?? false) {
            PengaturanAbsensi::query()->update(['aktif' => false]);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
