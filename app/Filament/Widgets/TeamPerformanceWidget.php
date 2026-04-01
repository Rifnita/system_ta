<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use App\Models\LaporanAktivitas;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TeamPerformanceWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $thisWeek = Carbon::now()->startOfWeek();
        $lastWeek = Carbon::now()->subWeek()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        $activeUsers = User::where('is_active', true)->count();

        $activeThisWeek = LaporanAktivitas::where('created_at', '>=', $thisWeek)
            ->distinct('user_id')
            ->count('user_id');

        $completedThisWeek = LaporanAktivitas::where('status', 'completed')
            ->where('updated_at', '>=', $thisWeek)
            ->count();

        $completedLastWeek = LaporanAktivitas::where('status', 'completed')
            ->whereBetween('updated_at', [$lastWeek, $thisWeek])
            ->count();

        $weekChange = $completedLastWeek > 0
            ? round((($completedThisWeek - $completedLastWeek) / $completedLastWeek) * 100, 1)
            : 0;

        $productivityRate = $activeThisWeek > 0
            ? round($completedThisWeek / $activeThisWeek, 1)
            : 0;

        $pendingTasks = LaporanAktivitas::where('status', 'pending')->count();

        $overdueTasks = LaporanAktivitas::whereNotNull('target_end_time')
            ->where('target_end_time', '<', Carbon::now())
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        $avgCompletionTime = LaporanAktivitas::where('status', 'completed')
            ->where('updated_at', '>=', $thisMonth)
            ->whereNotNull('tanggal_aktivitas')
            ->get()
            ->avg(function ($task) {
                if (! $task->tanggal_aktivitas) {
                    return 0;
                }

                $start = Carbon::parse($task->tanggal_aktivitas);
                $end = Carbon::parse($task->updated_at);

                return $start->diffInDays($end);
            });

        $avgDays = round($avgCompletionTime ?? 0, 1);

        return [
            Stat::make('Anggota Tim Aktif', $activeUsers)
                ->description("$activeThisWeek aktif minggu ini")
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([7, 12, 15, 18, 16, 19, $activeThisWeek]),

            Stat::make('Tugas Selesai Minggu Ini', $completedThisWeek)
                ->description($weekChange >= 0 ? "+$weekChange% dari minggu lalu" : "$weekChange% dari minggu lalu")
                ->descriptionIcon($weekChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($weekChange >= 0 ? 'success' : 'danger')
                ->chart([$completedLastWeek, $completedThisWeek]),

            Stat::make('Produktivitas Tim', $productivityRate . ' tugas/orang')
                ->description('Rata-rata per anggota aktif')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($productivityRate >= 5 ? 'success' : ($productivityRate >= 3 ? 'warning' : 'info')),

            Stat::make('Tugas Menunggu', $pendingTasks)
                ->description('Menunggu penugasan atau mulai kerja')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingTasks > 20 ? 'warning' : 'info'),

            Stat::make('Tugas Terlambat', $overdueTasks)
                ->description('Perlu perhatian segera')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueTasks > 0 ? 'danger' : 'success'),

            Stat::make('Rata-rata Waktu Selesai', $avgDays . ' hari')
                ->description('Rata-rata durasi penyelesaian')
                ->descriptionIcon('heroicon-m-clock')
                ->color($avgDays <= 3 ? 'success' : ($avgDays <= 7 ? 'warning' : 'danger')),
        ];
    }
}
