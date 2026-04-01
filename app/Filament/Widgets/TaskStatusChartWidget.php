<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use App\Models\LaporanAktivitas;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskStatusChartWidget extends ChartWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];
    protected ?string $maxHeight = '300px';

    public function getHeading(): ?string
    {
        return 'Distribusi Tugas Saya';
    }

    protected function getData(): array
    {
        $statusData = LaporanAktivitas::query()
            ->where('user_id', Auth::id())
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $pending = $statusData['pending'] ?? 0;
        $inProgress = $statusData['in_progress'] ?? 0;
        $completed = $statusData['completed'] ?? 0;
        $cancelled = $statusData['cancelled'] ?? 0;

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Tugas',
                    'data' => [$pending, $inProgress, $completed, $cancelled],
                    'backgroundColor' => [
                        'rgba(217, 222, 234, 0.85)',
                        'rgba(64, 91, 151, 0.72)',
                        'rgba(208, 173, 99, 0.72)',
                        'rgba(225, 29, 72, 0.62)',
                    ],
                    'borderColor' => [
                        'rgb(179, 189, 214)',
                        'rgb(47, 73, 127)',
                        'rgb(191, 165, 111)',
                        'rgb(190, 24, 93)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Menunggu', 'Sedang Dikerjakan', 'Selesai', 'Dibatalkan'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
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
            'maintainAspectRatio' => true,
        ];
    }
}
