<?php

namespace App\Filament\Widgets;

use App\Models\LaporanAktivitas;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskOverviewStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Only show for staff/users, hide from managers/admins
        $user = Auth::user();
        return $user && !$user->hasAnyRole(['super_admin', 'panel_user']);
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Query tasks for current user
        $userTasks = LaporanAktivitas::where('user_id', $user->id);
        
        $totalTasks = (clone $userTasks)->count();
        $pendingTasks = (clone $userTasks)->where('status', 'pending')->count();
        $inProgressTasks = (clone $userTasks)->where('status', 'in_progress')->count();
        $completedTasks = (clone $userTasks)->where('status', 'completed')->count();
        
        // Priority tasks
        $priorityTasks = (clone $userTasks)
            ->where('is_priority', true)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();
        
        // Overdue tasks (past target_end_time and not completed)
        $overdueTasks = (clone $userTasks)
            ->whereNotNull('target_end_time')
            ->where('target_end_time', '<', Carbon::now())
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();
        
        // Today's tasks
        $todayTasks = (clone $userTasks)
            ->whereDate('tanggal_aktivitas', Carbon::today())
            ->count();
        
        // Completion rate
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        return [
            Stat::make('Total Tasks', $totalTasks)
                ->description("$completedTasks completed · $pendingTasks pending")
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),
            
            Stat::make('In Progress', $inProgressTasks)
                ->description('Tasks being worked on')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),
            
            Stat::make('Priority Tasks', $priorityTasks)
                ->description('High priority pending')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($priorityTasks > 0 ? 'warning' : 'success'),
            
            Stat::make('Overdue', $overdueTasks)
                ->description('Past deadline')
                ->descriptionIcon('heroicon-m-clock')
                ->color($overdueTasks > 0 ? 'danger' : 'success'),
            
            Stat::make('Today\'s Tasks', $todayTasks)
                ->description('Scheduled for today')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
            
            Stat::make('Completion Rate', $completionRate . '%')
                ->description('Overall progress')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($completionRate >= 70 ? 'success' : ($completionRate >= 40 ? 'warning' : 'danger')),
        ];
    }
}
