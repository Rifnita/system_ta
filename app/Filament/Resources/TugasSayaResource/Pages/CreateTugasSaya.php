<?php

namespace App\Filament\Resources\TugasSayaResource\Pages;

use App\Filament\Resources\TugasSayaResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTugasSaya extends CreateRecord
{
    protected static string $resource = TugasSayaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Tugas Harian Berhasil Dibuat')
            ->body('Tugas harian Anda telah berhasil disimpan.');
    }
}
