<?php

namespace App\Filament\Resources\AbsensiResource\Pages;

use App\Filament\Resources\AbsensiResource;
use App\Models\Absensi;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListAbsensis extends ListRecords
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        $absensiHariIni = Absensi::absensiHariIni(Auth::id());
        $sudahAbsen = (bool) $absensiHariIni;
        $bisaAbsenKeluar = $absensiHariIni?->canCheckoutBy(Auth::user()) ?? false;
        
        return [
            Actions\Action::make('absen_masuk')
                ->label($sudahAbsen ? 'Sudah Absen Hari Ini' : 'Absen Masuk')
                ->icon('heroicon-o-arrow-left-on-rectangle')
                ->color($sudahAbsen ? 'gray' : 'success')
                ->disabled($sudahAbsen)
                ->url(fn () => AbsensiResource::getUrl('create'))
                ->visible(fn () => AbsensiResource::canDo('create')),
            Actions\Action::make('absen_keluar')
                ->label($bisaAbsenKeluar ? 'Absen Keluar' : 'Belum Bisa Absen Keluar')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->color($bisaAbsenKeluar ? 'warning' : 'gray')
                ->disabled(! $bisaAbsenKeluar)
                ->url(fn () => $absensiHariIni ? AbsensiResource::getUrl('edit', ['record' => $absensiHariIni]) : '#')
                ->visible(fn () => AbsensiResource::canDo('create') || AbsensiResource::canDo('update')),
        ];
    }
}
