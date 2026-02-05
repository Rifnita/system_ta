<?php

namespace App\Filament\Resources\LaporanAktivitasResource\Pages;

use App\Filament\Resources\LaporanAktivitasResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateLaporanAktivitas extends CreateRecord
{
    protected static string $resource = LaporanAktivitasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Laporan Aktivitas Berhasil Dibuat')
            ->body('Laporan aktivitas Anda telah berhasil disimpan.');
    }
}
