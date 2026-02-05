<?php

namespace App\Filament\Resources\LaporanAktivitasResource\Pages;

use App\Filament\Resources\LaporanAktivitasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditLaporanAktivitas extends EditRecord
{
    protected static string $resource = LaporanAktivitasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Laporan Aktivitas Berhasil Diupdate')
            ->body('Perubahan laporan aktivitas Anda telah berhasil disimpan.');
    }
}
