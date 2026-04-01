<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use App\Models\LaporanAktivitas;
use App\Models\LaporanMingguan;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class WeeklyComparisonWidget extends ChartWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];
    protected ?string $maxHeight = '300px';

    public function getHeading(): ?string
    {
        return 'Tren Kinerja Mingguan';
    }

    public function getDescription(): ?string
    {
        return 'Perbandingan 8 minggu terakhir';
    }

    protected function getData(): array
    {
        $weeks = [];
        $tasksCompleted = [];
        $reportsSubmitted = [];

        for ($i = 7; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();

            $weeks[] = $weekStart->translatedFormat('d M');

            $tasksCompleted[] = LaporanAktivitas::where('status', 'completed')
                ->whereBetween('updated_at', [$weekStart, $weekEnd])
                ->count();

            $reportsSubmitted[] = LaporanMingguan::whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Tugas Selesai',
                    'data' => $tasksCompleted,
                    'borderColor' => 'rgb(64, 91, 151)',
                    'backgroundColor' => 'rgba(64, 91, 151, 0.12)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Laporan Terkirim',
                    'data' => $reportsSubmitted,
                    'borderColor' => 'rgb(191, 165, 111)',
                    'backgroundColor' => 'rgba(191, 165, 111, 0.16)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => $weeks,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 5,
                    ],
                ],
            ],
            'maintainAspectRatio' => true,
        ];
    }
}
