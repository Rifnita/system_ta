<?php

namespace App\Filament\Widgets;

use App\Models\LaporanAktivitas;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskStatusChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];
    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        // Personal widget - only for staff/users
        $user = Auth::user();
        return $user && !$user->hasAnyRole(['super_admin', 'panel_user']);
    }

    public function getHeading(): ?string
    {
        return 'My Tasks By Status';
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
                    'label' => 'Tasks',
                    'data' => [$pending, $inProgress, $completed, $cancelled],
                    'backgroundColor' => [
                        'rgba(156, 163, 175, 0.5)',  // gray - pending
                        'rgba(59, 130, 246, 0.5)',   // blue - in progress
                        'rgba(34, 197, 94, 0.5)',    // green - completed
                        'rgba(239, 68, 68, 0.5)',    // red - cancelled
                    ],
                    'borderColor' => [
                        'rgb(156, 163, 175)',
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Pending', 'In Progress', 'Completed', 'Cancelled'],
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
