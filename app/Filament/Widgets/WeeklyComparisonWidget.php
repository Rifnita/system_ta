<?php

namespace App\Filament\Widgets;

use App\Models\LaporanAktivitas;
use App\Models\LaporanMingguan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WeeklyComparisonWidget extends ChartWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];
    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        // Only for managers and admins
        $user = Auth::user();
        return $user && $user->hasAnyRole(['super_admin', 'panel_user']);
    }

    public function getHeading(): ?string
    {
        return 'Weekly Performance Trend';
    }

    public function getDescription(): ?string
    {
        return 'Last 8 weeks comparison';
    }

    protected function getData(): array
    {
        $weeks = [];
        $tasksCompleted = [];
        $reportsSubmitted = [];

        for ($i = 7; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
            
            // Week label
            $weeks[] = $weekStart->format('M d');
            
            // Tasks completed this week
            $tasksCompleted[] = LaporanAktivitas::where('status', 'completed')
                ->whereBetween('updated_at', [$weekStart, $weekEnd])
                ->count();
            
            // Weekly reports submitted
            $reportsSubmitted[] = LaporanMingguan::whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Tasks Completed',
                    'data' => $tasksCompleted,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Reports Submitted',
                    'data' => $reportsSubmitted,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
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
