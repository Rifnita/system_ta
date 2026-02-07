<?php

namespace App\Filament\Widgets;

use App\Models\Proyek;
use App\Models\LaporanMingguan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectPortfolioWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Only for managers and admins
        $user = Auth::user();
        return $user && $user->hasAnyRole(['super_admin', 'panel_user']);
    }

    protected function getStats(): array
    {
        // Total projects by status
        $totalProjects = Proyek::count();
        $activeProjects = Proyek::where('status', Proyek::STATUS_DALAM_PENGERJAAN)->count();
        $completedProjects = Proyek::where('status', Proyek::STATUS_SELESAI)->count();
        $onHoldProjects = Proyek::where('status', Proyek::STATUS_TERTUNDA)->count();
        
        // Projects completion rate
        $completionRate = $totalProjects > 0 
            ? round(($completedProjects / $totalProjects) * 100, 1)
            : 0;
        
        // Average project progress (from laporan mingguan)
        $avgProgress = LaporanMingguan::selectRaw('proyek_id, MAX(persentase_penyelesaian) as max_progress')
            ->groupBy('proyek_id')
            ->get()
            ->avg('max_progress');
        
        $avgProgressRounded = round($avgProgress ?? 0, 1);
        
        // Projects on schedule (progress >= expected based on timeline)
        $onScheduleCount = 0;
        $behindScheduleCount = 0;
        
        foreach (Proyek::where('status', Proyek::STATUS_DALAM_PENGERJAAN)->get() as $project) {
            if ($project->tanggal_mulai && $project->estimasi_selesai) {
                $totalDays = Carbon::parse($project->tanggal_mulai)->diffInDays(Carbon::parse($project->estimasi_selesai));
                $elapsedDays = Carbon::parse($project->tanggal_mulai)->diffInDays(Carbon::now());
                $expectedProgress = $totalDays > 0 ? ($elapsedDays / $totalDays) * 100 : 0;
                
                $latestProgress = $project->laporanMingguan()->max('persentase_penyelesaian') ?? 0;
                
                if ($latestProgress >= $expectedProgress - 5) { // 5% tolerance
                    $onScheduleCount++;
                } else {
                    $behindScheduleCount++;
                }
            }
        }
        
        $scheduleAdherence = ($onScheduleCount + $behindScheduleCount) > 0
            ? round(($onScheduleCount / ($onScheduleCount + $behindScheduleCount)) * 100, 1)
            : 0;
        
        // Total contract value
        $totalValue = Proyek::sum('nilai_kontrak');
        $activeValue = Proyek::where('status', Proyek::STATUS_DALAM_PENGERJAAN)->sum('nilai_kontrak');
        
        // Projects starting/ending soon
        $startingSoon = Proyek::where('status', Proyek::STATUS_PERENCANAAN)
            ->whereNotNull('tanggal_mulai')
            ->where('tanggal_mulai', '<=', Carbon::now()->addWeeks(2))
            ->where('tanggal_mulai', '>=', Carbon::now())
            ->count();
        
        $endingSoon = Proyek::where('status', Proyek::STATUS_DALAM_PENGERJAAN)
            ->whereNotNull('estimasi_selesai')
            ->where('estimasi_selesai', '<=', Carbon::now()->addWeeks(4))
            ->where('estimasi_selesai', '>=', Carbon::now())
            ->count();

        return [
            Stat::make('Total Projects', $totalProjects)
                ->description("$activeProjects active · $completedProjects completed · $onHoldProjects on hold")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary')
                ->chart([$completedProjects, $activeProjects, $onHoldProjects]),
            
            Stat::make('Portfolio Completion', $completionRate . '%')
                ->description("$completedProjects of $totalProjects projects")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($completionRate >= 50 ? 'success' : 'info'),
            
            Stat::make('Avg Project Progress', $avgProgressRounded . '%')
                ->description('Across all active projects')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($avgProgressRounded >= 60 ? 'success' : ($avgProgressRounded >= 30 ? 'warning' : 'danger')),
            
            Stat::make('Schedule Adherence', $scheduleAdherence . '%')
                ->description("$onScheduleCount on track · $behindScheduleCount behind")
                ->descriptionIcon('heroicon-m-calendar')
                ->color($scheduleAdherence >= 80 ? 'success' : ($scheduleAdherence >= 60 ? 'warning' : 'danger')),
            
            Stat::make('Active Portfolio Value', 'Rp ' . number_format($activeValue / 1000000, 1) . 'M')
                ->description('Total: Rp ' . number_format($totalValue / 1000000, 1) . 'M')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
            
            Stat::make('Upcoming Milestones', $startingSoon + $endingSoon)
                ->description("$startingSoon starting · $endingSoon completing soon")
                ->descriptionIcon('heroicon-m-flag')
                ->color(($startingSoon + $endingSoon) > 5 ? 'warning' : 'info'),
        ];
    }
}
