<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use App\Models\LaporanAktivitas;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TaskOverviewStatsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = Auth::user();

        $userTasks = LaporanAktivitas::where('user_id', $user->id);

        $totalTasks = (clone $userTasks)->count();
        $pendingTasks = (clone $userTasks)->where('status', 'pending')->count();
        $inProgressTasks = (clone $userTasks)->where('status', 'in_progress')->count();
        $completedTasks = (clone $userTasks)->where('status', 'completed')->count();

        $priorityTasks = (clone $userTasks)
            ->where('is_priority', true)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        $overdueTasks = (clone $userTasks)
            ->whereNotNull('target_end_time')
            ->where('target_end_time', '<', Carbon::now())
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        $todayTasks = (clone $userTasks)
            ->whereDate('tanggal_aktivitas', Carbon::today())
            ->count();

        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        return [
            Stat::make('Total Tugas', $totalTasks)
                ->description("$completedTasks selesai | $pendingTasks menunggu")
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Sedang Dikerjakan', $inProgressTasks)
                ->description('Tugas yang sedang diproses')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),

            Stat::make('Tugas Prioritas', $priorityTasks)
                ->description('Prioritas tinggi yang belum selesai')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($priorityTasks > 0 ? 'warning' : 'success'),

            Stat::make('Terlambat', $overdueTasks)
                ->description('Melewati batas waktu')
                ->descriptionIcon('heroicon-m-clock')
                ->color($overdueTasks > 0 ? 'danger' : 'success'),

            Stat::make('Tugas Hari Ini', $todayTasks)
                ->description('Dijadwalkan untuk hari ini')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Tingkat Penyelesaian', $completionRate . '%')
                ->description('Progres keseluruhan')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($completionRate >= 70 ? 'success' : ($completionRate >= 40 ? 'warning' : 'danger')),
        ];
    }
}
