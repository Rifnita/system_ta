<?php

namespace App\Filament\Resources\LaporanHarianResource\Pages;

use App\Filament\Resources\LaporanHarianResource;
use App\Models\LaporanAktivitas;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListLaporanHarian extends ListRecords
{
    protected static string $resource = LaporanHarianResource::class;

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
        $userId = Auth::id();

        return [
            'semua' => Tab::make('Semua')
                ->badge(fn () => LaporanAktivitas::where('user_id', $userId)->count()),

            'hari_ini' => Tab::make('Hari Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('tanggal_aktivitas', today()))
                ->badge(fn () => LaporanAktivitas::where('user_id', $userId)->whereDate('tanggal_aktivitas', today())->count()),

            'minggu_ini' => Tab::make('Minggu Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('tanggal_aktivitas', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]))
                ->badge(fn () => LaporanAktivitas::where('user_id', $userId)->whereBetween('tanggal_aktivitas', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ])->count()),

            'bulan_ini' => Tab::make('Bulan Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereMonth('tanggal_aktivitas', now()->month)
                    ->whereYear('tanggal_aktivitas', now()->year))
                ->badge(fn () => LaporanAktivitas::where('user_id', $userId)
                    ->whereMonth('tanggal_aktivitas', now()->month)
                    ->whereYear('tanggal_aktivitas', now()->year)
                    ->count()),
        ];
    }
}
