<?php

namespace App\Filament\Widgets;

use App\Models\LaporanAktivitas;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaskPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 22;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Personal performance metrics - only for staff/users
        $user = Auth::user();
        return $user && !$user->hasAnyRole(['super_admin', 'panel_user']);
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        
        // Tasks completed today
        $completedToday = LaporanAktivitas::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('updated_at', $today)
            ->count();
        
        // Tasks completed this week
        $completedThisWeek = LaporanAktivitas::where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('updated_at', '>=', $thisWeek)
            ->count();
        
        // Tasks completed this month
        $completedThisMonth = LaporanAktivitas::where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('updated_at', '>=', $thisMonth)
            ->count();
        
        // On-time completion rate (completed before or on deadline)
        $tasksWithDeadline = LaporanAktivitas::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('target_end_time')
            ->where('updated_at', '>=', $thisMonth)
            ->get();
        
        $onTimeCount = $tasksWithDeadline->filter(function ($task) {
            return $task->updated_at <= $task->target_end_time;
        })->count();
        
        $onTimeRate = $tasksWithDeadline->count() > 0 
            ? round(($onTimeCount / $tasksWithDeadline->count()) * 100, 1) 
            : 0;
        
        // Average daily completed tasks (this month)
        $daysInMonth = Carbon::now()->diffInDays($thisMonth) + 1;
        $avgDaily = $daysInMonth > 0 ? round($completedThisMonth / $daysInMonth, 1) : 0;
        
        // Total hours worked this week
        $totalHoursThisWeek = LaporanAktivitas::where('user_id', $user->id)
            ->where('updated_at', '>=', $thisWeek)
            ->whereNotNull('waktu_mulai')
            ->whereNotNull('waktu_selesai')
            ->get()
            ->sum(function ($task) {
                if (!$task->waktu_mulai || !$task->waktu_selesai) {
                    return 0;
                }
                
                try {
                    $mulai = Carbon::hasFormat((string) $task->waktu_mulai, 'H:i:s')
                        ? Carbon::createFromFormat('H:i:s', (string) $task->waktu_mulai)
                        : Carbon::createFromFormat('H:i', (string) $task->waktu_mulai);
                    
                    $selesai = Carbon::hasFormat((string) $task->waktu_selesai, 'H:i:s')
                        ? Carbon::createFromFormat('H:i:s', (string) $task->waktu_selesai)
                        : Carbon::createFromFormat('H:i', (string) $task->waktu_selesai);
                    
                    return $mulai->diffInMinutes($selesai) / 60;
                } catch (\Exception $e) {
                    return 0;
                }
            });

        return [
            Stat::make('Completed Today', $completedToday)
                ->description("$completedThisWeek this week · $completedThisMonth this month")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('Weekly Progress', $completedThisWeek . ' tasks')
                ->description('Tasks completed this week')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
            
            Stat::make('On-Time Rate', $onTimeRate . '%')
                ->description('Completed before deadline')
                ->descriptionIcon('heroicon-m-clock')
                ->color($onTimeRate >= 80 ? 'success' : ($onTimeRate >= 60 ? 'warning' : 'danger')),
            
            Stat::make('Avg Daily Tasks', $avgDaily)
                ->description('Average per day this month')
                ->descriptionIcon('heroicon-m-chart-bar-square')
                ->color($avgDaily >= 3 ? 'success' : 'info'),
            
            Stat::make('Hours This Week', round($totalHoursThisWeek, 1) . ' hrs')
                ->description('Total time tracked')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),
        ];
    }
}
