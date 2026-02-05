<?php

namespace App\Filament\Resources\LaporanHarianResource\Pages;

use App\Filament\Resources\LaporanHarianResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateLaporanHarian extends CreateRecord
{
    protected static string $resource = LaporanHarianResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Laporan Harian Berhasil Dibuat')
            ->body('Laporan harian Anda telah berhasil disimpan.');
    }
}
