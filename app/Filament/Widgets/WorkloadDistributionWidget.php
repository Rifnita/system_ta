<?php

namespace App\Filament\Widgets;

use App\Models\LaporanAktivitas;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkloadDistributionWidget extends ChartWidget
{
    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 'full';
    protected ?string $maxHeight = '350px';

    public static function canView(): bool
    {
        // Only for managers and admins
        $user = Auth::user();
        return $user && $user->hasAnyRole(['super_admin', 'panel_user']);
    }

    public function getHeading(): ?string
    {
        return 'Team Workload Distribution (Active Tasks)';
    }

    public function getDescription(): ?string
    {
        $totalActiveTasks = LaporanAktivitas::whereIn('status', ['pending', 'in_progress'])->count();
        $activeUsers = User::where('is_active', true)->count();
        
        return "Total active tasks: $totalActiveTasks across $activeUsers team members";
    }

    protected function getData(): array
    {
        // Get workload per user (tasks in pending or in_progress)
        $workloadData = LaporanAktivitas::query()
            ->whereIn('status', ['pending', 'in_progress'])
            ->select('user_id', DB::raw('count(*) as task_count'))
            ->with('user:id,name')
            ->groupBy('user_id')
            ->orderByDesc('task_count')
            ->limit(15)
            ->get();

        $userNames = [];
        $taskCounts = [];
        $backgroundColors = [];

        foreach ($workloadData as $data) {
            $userName = $data->user->name ?? 'Unknown User';
            $userNames[] = strlen($userName) > 20 ? substr($userName, 0, 17) . '...' : $userName;
            $taskCounts[] = $data->task_count;
            
            // Color coding based on workload
            if ($data->task_count > 20) {
                $backgroundColors[] = 'rgba(239, 68, 68, 0.6)'; // red - overloaded
            } elseif ($data->task_count > 10) {
                $backgroundColors[] = 'rgba(251, 191, 36, 0.6)'; // yellow - high load
            } elseif ($data->task_count > 5) {
                $backgroundColors[] = 'rgba(59, 130, 246, 0.6)'; // blue - moderate
            } else {
                $backgroundColors[] = 'rgba(34, 197, 94, 0.6)'; // green - light load
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Active Tasks',
                    'data' => $taskCounts,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => array_map(function($color) {
                        return str_replace('0.6', '1', $color);
                    }, $backgroundColors),
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $userNames,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // Horizontal bar chart
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Number of Active Tasks',
                    ],
                    'ticks' => [
                        'stepSize' => 5,
                    ],
                ],
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Team Member',
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
