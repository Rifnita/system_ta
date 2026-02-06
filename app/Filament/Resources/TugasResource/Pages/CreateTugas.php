<?php

namespace App\Filament\Resources\TugasResource\Pages;

use App\Filament\Resources\TugasResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateTugas extends CreateRecord
{
    protected static string $resource = TugasResource::class;

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
