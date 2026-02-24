<?php

namespace App\Filament\Resources\PengaturanAbsensiResource\Pages;

use App\Filament\Resources\PengaturanAbsensiResource;
use App\Models\PengaturanAbsensi;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengaturanAbsensi extends EditRecord
{
    protected static string $resource = PengaturanAbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Jika pengaturan diset aktif, nonaktifkan yang lain
        if ($data['aktif'] ?? false) {
            PengaturanAbsensi::where('id', '!=', $this->record->id)
                ->update(['aktif' => false]);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
