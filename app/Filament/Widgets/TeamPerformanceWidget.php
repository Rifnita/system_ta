<?php

namespace App\Filament\Widgets;

use App\Models\LaporanAktivitas;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeamPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Only for managers and admins
        $user = Auth::user();
        return $user && $user->hasAnyRole(['super_admin', 'panel_user']);
    }

    protected function getStats(): array
    {
        $thisWeek = Carbon::now()->startOfWeek();
        $lastWeek = Carbon::now()->subWeek()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        
        // Total active team members
        $activeUsers = User::where('is_active', true)->count();
        
        // Users with activity this week
        $activeThisWeek = LaporanAktivitas::where('created_at', '>=', $thisWeek)
            ->distinct('user_id')
            ->count('user_id');
        
        // Total tasks completed this week
        $completedThisWeek = LaporanAktivitas::where('status', 'completed')
            ->where('updated_at', '>=', $thisWeek)
            ->count();
        
        // Total tasks completed last week
        $completedLastWeek = LaporanAktivitas::where('status', 'completed')
            ->whereBetween('updated_at', [$lastWeek, $thisWeek])
            ->count();
        
        // Calculate week-over-week change
        $weekChange = $completedLastWeek > 0 
            ? round((($completedThisWeek - $completedLastWeek) / $completedLastWeek) * 100, 1)
            : 0;
        
        // Team productivity rate (tasks per active user this week)
        $productivityRate = $activeThisWeek > 0 
            ? round($completedThisWeek / $activeThisWeek, 1)
            : 0;
        
        // Pending tasks requiring attention
        $pendingTasks = LaporanAktivitas::where('status', 'pending')
            ->count();
        
        // Overdue tasks across team
        $overdueTasks = LaporanAktivitas::whereNotNull('target_end_time')
            ->where('target_end_time', '<', Carbon::now())
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();
        
        // Average task completion time this month (in days)
        $avgCompletionTime = LaporanAktivitas::where('status', 'completed')
            ->where('updated_at', '>=', $thisMonth)
            ->whereNotNull('tanggal_aktivitas')
            ->get()
            ->avg(function ($task) {
                if (!$task->tanggal_aktivitas) return 0;
                $start = Carbon::parse($task->tanggal_aktivitas);
                $end = Carbon::parse($task->updated_at);
                return $start->diffInDays($end);
            });
        
        $avgDays = round($avgCompletionTime ?? 0, 1);

        return [
            Stat::make('Active Team Members', $activeUsers)
                ->description("$activeThisWeek active this week")
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([7, 12, 15, 18, 16, 19, $activeThisWeek]),
            
            Stat::make('Tasks Completed This Week', $completedThisWeek)
                ->description($weekChange >= 0 ? "+$weekChange% from last week" : "$weekChange% from last week")
                ->descriptionIcon($weekChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($weekChange >= 0 ? 'success' : 'danger')
                ->chart([$completedLastWeek, $completedThisWeek]),
            
            Stat::make('Team Productivity', $productivityRate . ' tasks/person')
                ->description('Average per active user')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($productivityRate >= 5 ? 'success' : ($productivityRate >= 3 ? 'warning' : 'info')),
            
            Stat::make('Pending Tasks', $pendingTasks)
                ->description('Awaiting assignment/start')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingTasks > 20 ? 'warning' : 'info'),
            
            Stat::make('Overdue Tasks', $overdueTasks)
                ->description('Requires immediate attention')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueTasks > 0 ? 'danger' : 'success'),
            
            Stat::make('Avg Completion Time', $avgDays . ' days')
                ->description('Average task duration')
                ->descriptionIcon('heroicon-m-clock')
                ->color($avgDays <= 3 ? 'success' : ($avgDays <= 7 ? 'warning' : 'danger')),
        ];
    }
}
