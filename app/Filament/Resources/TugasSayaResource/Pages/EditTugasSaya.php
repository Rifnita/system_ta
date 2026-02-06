<?php

namespace App\Filament\Resources\TugasSayaResource\Pages;

use App\Filament\Resources\TugasSayaResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditTugasSaya extends EditRecord
{
    protected static string $resource = TugasSayaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => Auth::user()?->can('delete', $this->getRecord()) ?? false),
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
            ->title('Task Harian Berhasil Diupdate')
            ->body('Perubahan task harian Anda telah berhasil disimpan.');
    }
}
