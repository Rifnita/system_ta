<?php

namespace App\Filament\Resources\LaporanAktivitasResource\Pages;

use App\Filament\Resources\LaporanAktivitasResource;
use App\Models\LaporanAktivitas;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

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

    public function viewSchema(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.resources.laporan-aktivitas-resource.view-detail')
                    ->columnSpanFull(),
            ]);
    }
}
