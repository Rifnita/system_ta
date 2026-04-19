<?php

namespace App\Filament\Resources\AbsensiResource\Pages;

use App\Filament\Resources\AbsensiResource;
use App\Models\Absensi;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class ListAbsensis extends ListRecords
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        $absensiHariIni = Absensi::absensiHariIni(Auth::id());
        $sudahAbsen = (bool) $absensiHariIni;
        $bisaAbsenKeluar = $absensiHariIni?->canCheckoutBy(Auth::user()) ?? false;
        
        return [
            Actions\Action::make('export_rekap_excel')
                ->label('Rekap & Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->visible(fn (): bool => (bool) (Auth::user()?->hasRole('super_admin')))
                ->form([
                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->default(now()->startOfMonth())
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('Tanggal Selesai')
                        ->default(now()->endOfMonth())
                        ->required(),
                    Select::make('user_id')
                        ->label('Pegawai')
                        ->searchable()
                        ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->toArray())
                        ->placeholder('Semua pegawai')
                        ->visible(fn (): bool => (bool) (Auth::user()?->hasRole('super_admin'))),
                ])
                ->action(function (array $data): void {
                    $url = URL::route('admin.absensi.export.excel', array_filter([
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
                        'user_id' => $data['user_id'] ?? null,
                    ], fn ($value) => filled($value)));

                    $this->redirect($url, navigate: false);
                }),
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
