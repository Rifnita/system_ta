<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use App\Models\LaporanAktivitas;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TaskPerformanceWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 22;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = Auth::user();
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        $completedToday = LaporanAktivitas::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('updated_at', $today)
            ->count();

        $completedThisWeek = LaporanAktivitas::where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('updated_at', '>=', $thisWeek)
            ->count();

        $completedThisMonth = LaporanAktivitas::where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('updated_at', '>=', $thisMonth)
            ->count();

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

        $daysInMonth = Carbon::now()->diffInDays($thisMonth) + 1;
        $avgDaily = $daysInMonth > 0 ? round($completedThisMonth / $daysInMonth, 1) : 0;

        $totalHoursThisWeek = LaporanAktivitas::where('user_id', $user->id)
            ->where('updated_at', '>=', $thisWeek)
            ->whereNotNull('waktu_mulai')
            ->whereNotNull('waktu_selesai')
            ->get()
            ->sum(function ($task) {
                if (! $task->waktu_mulai || ! $task->waktu_selesai) {
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
            Stat::make('Selesai Hari Ini', $completedToday)
                ->description("$completedThisWeek minggu ini | $completedThisMonth bulan ini")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Progres Mingguan', $completedThisWeek . ' tugas')
                ->description('Jumlah tugas yang selesai minggu ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Tepat Waktu', $onTimeRate . '%')
                ->description('Selesai sebelum batas waktu')
                ->descriptionIcon('heroicon-m-clock')
                ->color($onTimeRate >= 80 ? 'success' : ($onTimeRate >= 60 ? 'warning' : 'danger')),

            Stat::make('Rata-rata Tugas Harian', $avgDaily)
                ->description('Rata-rata per hari di bulan ini')
                ->descriptionIcon('heroicon-m-chart-bar-square')
                ->color($avgDaily >= 3 ? 'success' : 'info'),

            Stat::make('Jam Kerja Minggu Ini', round($totalHoursThisWeek, 1) . ' jam')
                ->description('Total waktu yang tercatat')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),
        ];
    }
}
