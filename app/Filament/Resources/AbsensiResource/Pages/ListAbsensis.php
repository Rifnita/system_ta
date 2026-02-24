<?php

namespace App\Filament\Resources\AbsensiResource\Pages;

use App\Filament\Resources\AbsensiResource;
use App\Models\Absensi;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ListAbsensis extends ListRecords
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        $sudahAbsen = Absensi::sudahAbsenHariIni(Auth::id());
        
        return [
            Actions\Action::make('absen_masuk')
                ->label($sudahAbsen ? 'Sudah Absen Hari Ini' : 'Absen Masuk')
                ->icon('heroicon-o-arrow-left-on-rectangle')
                ->color($sudahAbsen ? 'gray' : 'success')
                ->disabled($sudahAbsen)
                ->url(fn () => AbsensiResource::getUrl('create'))
                ->visible(fn () => Auth::user()->can('create_absensi')),
        ];
    }
}
