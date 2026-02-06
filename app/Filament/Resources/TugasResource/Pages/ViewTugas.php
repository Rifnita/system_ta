<?php

namespace App\Filament\Resources\TugasResource\Pages;

use App\Filament\Resources\TugasResource;
use App\Models\LaporanAktivitas;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTugas extends ViewRecord
{
    protected static string $resource = TugasResource::class;

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
