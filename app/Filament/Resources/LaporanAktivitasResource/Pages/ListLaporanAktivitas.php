<?php

namespace App\Filament\Resources\LaporanAktivitasResource\Pages;

use App\Filament\Resources\LaporanAktivitasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLaporanAktivitas extends ListRecords
{
    protected static string $resource = LaporanAktivitasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Laporan')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Aktivitas')
                ->badge(fn () => \App\Models\LaporanAktivitas::count()),
            
            'hari_ini' => Tab::make('Hari Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('tanggal_aktivitas', today()))
                ->badge(fn () => \App\Models\LaporanAktivitas::whereDate('tanggal_aktivitas', today())->count()),
            
            'minggu_ini' => Tab::make('Minggu Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('tanggal_aktivitas', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]))
                ->badge(fn () => \App\Models\LaporanAktivitas::whereBetween('tanggal_aktivitas', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count()),
            
            'bulan_ini' => Tab::make('Bulan Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereMonth('tanggal_aktivitas', now()->month)
                    ->whereYear('tanggal_aktivitas', now()->year))
                ->badge(fn () => \App\Models\LaporanAktivitas::whereMonth('tanggal_aktivitas', now()->month)
                    ->whereYear('tanggal_aktivitas', now()->year)->count()),
        ];
    }
}
