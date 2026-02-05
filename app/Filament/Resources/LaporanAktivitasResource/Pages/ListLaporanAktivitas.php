<?php

namespace App\Filament\Resources\LaporanAktivitasResource\Pages;

use App\Filament\Resources\LaporanAktivitasResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListLaporanAktivitas extends ListRecords
{
    protected static string $resource = LaporanAktivitasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->modalHeading('Export Laporan Aktivitas (PDF)')
                ->form([
                    Select::make('periode')
                        ->label('Periode')
                        ->options([
                            'harian' => '1 Hari',
                            'mingguan' => '1 Minggu',
                            'bulanan' => '1 Bulan',
                        ])
                        ->default('harian')
                        ->required()
                        ->live(),

                    DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->native(false)
                        ->required(fn ($get) => in_array($get('periode'), ['harian', 'mingguan'], true))
                        ->visible(fn ($get) => in_array($get('periode'), ['harian', 'mingguan'], true)),

                    Select::make('bulan')
                        ->label('Bulan')
                        ->options([
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember',
                        ])
                        ->default((int) now()->month)
                        ->required(fn ($get) => $get('periode') === 'bulanan')
                        ->visible(fn ($get) => $get('periode') === 'bulanan'),

                    Select::make('tahun')
                        ->label('Tahun')
                        ->options(function (): array {
                            $year = (int) now()->year;
                            $years = range($year - 5, $year + 1);
                            return array_combine($years, $years);
                        })
                        ->default((int) now()->year)
                        ->required(fn ($get) => $get('periode') === 'bulanan')
                        ->visible(fn ($get) => $get('periode') === 'bulanan'),

                    Select::make('user_id')
                        ->label('Pegawai (opsional)')
                        ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->visible(fn () => Auth::user()?->can('view_any_laporan::aktivitas') === true),
                ])
                ->action(function (array $data) {
                    if (Auth::user()?->can('view_any_laporan::aktivitas') !== true) {
                        unset($data['user_id']);
                    }

                    // Livewire tidak bisa mengirim binary PDF langsung dalam response JSON.
                    // Jadi kita redirect ke route download.
                    return redirect()->route('admin.laporan-aktivitas.export.pdf', array_filter([
                        'periode' => $data['periode'] ?? 'harian',
                        'tanggal' => $data['tanggal'] ?? null,
                        'bulan' => $data['bulan'] ?? null,
                        'tahun' => $data['tahun'] ?? null,
                        'user_id' => $data['user_id'] ?? null,
                    ], fn ($value) => $value !== null && $value !== ''));
                }),
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
