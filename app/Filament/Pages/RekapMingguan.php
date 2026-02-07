<?php

namespace App\Filament\Pages;

use App\Models\Proyek;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

class RekapMingguan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Weekly Summary';

    protected static ?string $title = 'Weekly Reports Summary';

    protected static ?int $navigationSort = 3;

    protected static string | \UnitEnum | null $navigationGroup = 'Reports & Projects';

    public $proyekId = null;
    public $tahun = null;
    public $tanggalMulai = null;
    public $tanggalAkhir = null;

    protected string $view = 'filament.pages.rekap-mingguan';

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
                    ->label('Filter Project')
                    ->options(Proyek::pluck('nama_proyek', 'id'))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->placeholder('All Projects'),
                
                Select::make('tahun')
                    ->label('Year')
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
                    ->label('From Date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->maxDate(fn ($get) => $get('tanggalAkhir')),
                
                DatePicker::make('tanggalAkhir')
                    ->label('To Date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->minDate(fn ($get) => $get('tanggalMulai')),
            ])
            ->columns(4)
            ->statePath('data');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\RekapMingguanStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\KualitasLaporanChartWidget::class,
            \App\Filament\Widgets\ProgressTrendChartWidget::class,
            \App\Filament\Widgets\TopProyekProgressWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 4;
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'xl' => 2,
        ];
    }
}
