<?php

namespace App\Filament\Pages;

use App\Models\Proyek;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

class RekapMingguan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Rekap Mingguan';

    protected string $view = 'filament.pages.rekap-mingguan';

    protected static ?string $title = 'Rekap Laporan Mingguan';

    protected static ?int $navigationSort = 3;

    public $proyekId = null;
    public $tahun = null;
    public $tanggalMulai = null;
    public $tanggalAkhir = null;

    public function mount(): void
    {
        $this->form->fill([
            'proyekId' => null,
            'tahun' => date('Y'),
            'tanggalMulai' => now()->startOfYear()->format('Y-m-d'),
            'tanggalAkhir' => now()->format('Y-m-d'),
        ]);
    }

    public function form($form)
    {
        return $form
            ->schema([
                Select::make('proyekId')
                    ->label('Filter Proyek')
                    ->options(Proyek::pluck('nama_proyek', 'id'))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->placeholder('Semua Proyek'),
                
                Select::make('tahun')
                    ->label('Tahun')
                    ->options(function () {
                        $years = [];
                        for ($i = date('Y'); $i >= 2020; $i--) {
                            $years[$i] = $i;
                        }
                        return $years;
                    })
                    ->default(date('Y'))
                    ->native(false),
                
                DatePicker::make('tanggalMulai')
                    ->label('Dari Tanggal')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->maxDate(fn ($get) => $get('tanggalAkhir')),
                
                DatePicker::make('tanggalAkhir')
                    ->label('Sampai Tanggal')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->minDate(fn ($get) => $get('tanggalMulai')),
            ])
            ->columns(4)
            ->statePath('data');
    }
}
