<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use App\Models\LaporanMingguan;
use App\Models\Proyek;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProjectPortfolioWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalProjects = Proyek::count();
        $activeProjects = Proyek::where('status', Proyek::STATUS_DALAM_PENGERJAAN)->count();
        $completedProjects = Proyek::where('status', Proyek::STATUS_SELESAI)->count();
        $onHoldProjects = Proyek::where('status', Proyek::STATUS_TERTUNDA)->count();

        $completionRate = $totalProjects > 0
            ? round(($completedProjects / $totalProjects) * 100, 1)
            : 0;

        $avgProgress = LaporanMingguan::selectRaw('proyek_id, MAX(persentase_penyelesaian) as max_progress')
            ->groupBy('proyek_id')
            ->get()
            ->avg('max_progress');

        $avgProgressRounded = round($avgProgress ?? 0, 1);

        $onScheduleCount = 0;
        $behindScheduleCount = 0;

        foreach (Proyek::where('status', Proyek::STATUS_DALAM_PENGERJAAN)->get() as $project) {
            if ($project->tanggal_mulai && $project->estimasi_selesai) {
                $totalDays = Carbon::parse($project->tanggal_mulai)->diffInDays(Carbon::parse($project->estimasi_selesai));
                $elapsedDays = Carbon::parse($project->tanggal_mulai)->diffInDays(Carbon::now());
                $expectedProgress = $totalDays > 0 ? ($elapsedDays / $totalDays) * 100 : 0;

                $latestProgress = $project->laporanMingguan()->max('persentase_penyelesaian') ?? 0;

                if ($latestProgress >= $expectedProgress - 5) {
                    $onScheduleCount++;
                } else {
                    $behindScheduleCount++;
                }
            }
        }

        $scheduleAdherence = ($onScheduleCount + $behindScheduleCount) > 0
            ? round(($onScheduleCount / ($onScheduleCount + $behindScheduleCount)) * 100, 1)
            : 0;

        $totalValue = Proyek::sum('nilai_kontrak');
        $activeValue = Proyek::where('status', Proyek::STATUS_DALAM_PENGERJAAN)->sum('nilai_kontrak');

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
            Stat::make('Total Proyek', $totalProjects)
                ->description("$activeProjects aktif | $completedProjects selesai | $onHoldProjects tertunda")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary')
                ->chart([$completedProjects, $activeProjects, $onHoldProjects]),

            Stat::make('Penyelesaian Portofolio', $completionRate . '%')
                ->description("$completedProjects dari $totalProjects proyek")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($completionRate >= 50 ? 'success' : 'info'),

            Stat::make('Rata-rata Progres Proyek', $avgProgressRounded . '%')
                ->description('Akumulasi seluruh proyek aktif')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($avgProgressRounded >= 60 ? 'success' : ($avgProgressRounded >= 30 ? 'warning' : 'danger')),

            Stat::make('Kepatuhan Jadwal', $scheduleAdherence . '%')
                ->description("$onScheduleCount sesuai target | $behindScheduleCount tertinggal")
                ->descriptionIcon('heroicon-m-calendar')
                ->color($scheduleAdherence >= 80 ? 'success' : ($scheduleAdherence >= 60 ? 'warning' : 'danger')),

            Stat::make('Nilai Portofolio Aktif', 'Rp ' . number_format($activeValue / 1000000, 1) . 'M')
                ->description('Total keseluruhan: Rp ' . number_format($totalValue / 1000000, 1) . 'M')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Milestone Mendatang', $startingSoon + $endingSoon)
                ->description("$startingSoon akan mulai | $endingSoon segera selesai")
                ->descriptionIcon('heroicon-m-flag')
                ->color(($startingSoon + $endingSoon) > 5 ? 'warning' : 'info'),
        ];
    }
}
