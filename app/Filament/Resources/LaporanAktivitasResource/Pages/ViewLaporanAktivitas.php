<?php

namespace App\Filament\Resources\LaporanAktivitasResource\Pages;

use App\Filament\Resources\LaporanAktivitasResource;
use App\Models\LaporanAktivitas;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLaporanAktivitas extends ViewRecord
{
    protected static string $resource = LaporanAktivitasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (LaporanAktivitas $record) {
                    return redirect()->route('admin.laporan-aktivitas.export.single.pdf', $record);
                }),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
