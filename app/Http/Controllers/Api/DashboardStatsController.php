<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanAktivitas;
use App\Models\LaporanMingguan;
use App\Models\Proyek;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardStatsController extends Controller
{
    public function index(): JsonResponse
    {
        $totalTugas = LaporanAktivitas::query()->count();
        $tasksInProgress = LaporanAktivitas::query()->where('status', 'in_progress')->count();
        $priorityTasks = LaporanAktivitas::query()->where('is_priority', true)->count();
        $overdueTasks = LaporanAktivitas::query()
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('target_end_time')
            ->where('target_end_time', '<', now())
            ->count();
        $tasksToday = LaporanAktivitas::query()->whereDate('tanggal_aktivitas', now()->toDateString())->count();
        $completedTasks = LaporanAktivitas::query()->where('status', 'completed')->count();

        $totalProyek = Proyek::query()->count();
        $projectStatusCounts = Proyek::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalLaporanMingguan = LaporanMingguan::query()->count();
        $averageProjectProgress = (float) (LaporanMingguan::query()->avg('persentase_penyelesaian') ?? 0);

        $eligibleTargetReports = LaporanMingguan::query()
            ->whereNotNull('target_mingguan')
            ->where('target_mingguan', '>', 0)
            ->whereNotNull('realisasi_mingguan');

        $eligibleCount = (clone $eligibleTargetReports)->count();
        $achievedCount = (clone $eligibleTargetReports)
            ->whereColumn('realisasi_mingguan', '>=', 'target_mingguan')
            ->count();

        $targetTercapai = $eligibleCount > 0
            ? round(($achievedCount / $eligibleCount) * 100, 1)
            : 0;

        $taskCompletionRate = $totalTugas > 0
            ? round(($completedTasks / $totalTugas) * 100, 1)
            : 0;

        $trendKinerjaMingguan = $this->buildWeeklyPerformanceTrend();
        $progressTugasMingguan = $this->buildWeeklyTaskProgress();
        $topProjects = $this->buildTopProjects();
        $upcomingDeadline = $this->buildUpcomingDeadlines();
        $recentTasks = $this->buildRecentTasks();

        return response()->json([
            'data' => [
                'total_tugas' => $totalTugas,
                'tugas_sedang_dikerjakan' => $tasksInProgress,
                'tugas_prioritas' => $priorityTasks,
                'tugas_terlambat' => $overdueTasks,
                'tugas_hari_ini' => $tasksToday,
                'tugas_selesai' => $completedTasks,
                'tingkat_penyelesaian_tugas' => $taskCompletionRate,
                'total_proyek' => $totalProyek,
                'rata_rata_progress_proyek' => round($averageProjectProgress, 1),
                'rata_rata_progress_keseluruhan' => round($averageProjectProgress, 1),
                'total_proyek_berdasarkan_status' => [
                    'perencanaan' => (int) ($projectStatusCounts['perencanaan'] ?? 0),
                    'dalam_pengerjaan' => (int) ($projectStatusCounts['dalam_pengerjaan'] ?? 0),
                    'tertunda' => (int) ($projectStatusCounts['tertunda'] ?? 0),
                    'selesai' => (int) ($projectStatusCounts['selesai'] ?? 0),
                ],
                'distribusi_proyek_berdasarkan_status' => [
                    'perencanaan' => (int) ($projectStatusCounts['perencanaan'] ?? 0),
                    'dalam_pengerjaan' => (int) ($projectStatusCounts['dalam_pengerjaan'] ?? 0),
                    'tertunda' => (int) ($projectStatusCounts['tertunda'] ?? 0),
                    'selesai' => (int) ($projectStatusCounts['selesai'] ?? 0),
                ],
                'total_laporan_mingguan' => $totalLaporanMingguan,
                'target_tercapai' => $targetTercapai,
                'tren_kinerja_mingguan' => $trendKinerjaMingguan,
                'progress_tugas_mingguan' => $progressTugasMingguan,
                'top_proyek_progress' => $topProjects,
                'upcoming_deadline' => $upcomingDeadline,
                'recent_tasks' => $recentTasks,
            ],
        ]);
    }

    private function buildWeeklyPerformanceTrend(): array
    {
        $rows = LaporanMingguan::query()
            ->selectRaw('YEAR(tanggal_akhir) as year_num')
            ->selectRaw('WEEK(tanggal_akhir, 3) as week_num')
            ->selectRaw('AVG(persentase_penyelesaian) as avg_progress')
            ->selectRaw('AVG(realisasi_mingguan) as avg_realisasi')
            ->whereNotNull('tanggal_akhir')
            ->groupByRaw('YEAR(tanggal_akhir), WEEK(tanggal_akhir, 3)')
            ->orderByRaw('YEAR(tanggal_akhir) desc, WEEK(tanggal_akhir, 3) desc')
            ->limit(8)
            ->get()
            ->reverse()
            ->values();

        return $rows->map(function ($row): array {
            $year = (int) $row->year_num;
            $week = (int) $row->week_num;

            return [
                'label' => sprintf('Minggu %d - %d', $week, $year),
                'rata_rata_progress' => round((float) ($row->avg_progress ?? 0), 1),
                'rata_rata_realisasi' => round((float) ($row->avg_realisasi ?? 0), 1),
            ];
        })->all();
    }

    private function buildWeeklyTaskProgress(): array
    {
        $result = [];

        for ($offset = 7; $offset >= 0; $offset--) {
            $startOfWeek = CarbonImmutable::now()->startOfWeek()->subWeeks($offset);
            $endOfWeek = $startOfWeek->endOfWeek();

            $tasksCompleted = LaporanAktivitas::query()
                ->where('status', 'completed')
                ->whereBetween('tanggal_aktivitas', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->count();

            $reportsSent = LaporanMingguan::query()
                ->whereBetween('tanggal_akhir', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->count();

            $result[] = [
                'label' => sprintf('Minggu %d - %d', (int) $startOfWeek->isoWeek(), (int) $startOfWeek->year),
                'tugas_selesai' => $tasksCompleted,
                'laporan_terkirim' => $reportsSent,
            ];
        }

        return $result;
    }

    private function buildTopProjects(): array
    {
        return Proyek::query()
            ->leftJoin('laporan_mingguan as lm', 'lm.proyek_id', '=', 'proyek.id')
            ->select('proyek.id', 'proyek.nama_proyek', 'proyek.status')
            ->selectRaw('COALESCE(MAX(lm.persentase_penyelesaian), 0) as progress')
            ->groupBy('proyek.id', 'proyek.nama_proyek', 'proyek.status')
            ->orderByDesc('progress')
            ->limit(5)
            ->get()
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'nama_proyek' => $row->nama_proyek,
                'status' => $row->status,
                'progress' => round((float) ($row->progress ?? 0), 1),
            ])
            ->all();
    }

    private function buildUpcomingDeadlines(): array
    {
        return Proyek::query()
            ->whereNotNull('estimasi_selesai')
            ->whereDate('estimasi_selesai', '>=', now()->toDateString())
            ->orderBy('estimasi_selesai')
            ->limit(5)
            ->get()
            ->map(function (Proyek $project): array {
                $deadline = CarbonImmutable::parse($project->estimasi_selesai);

                return [
                    'id' => (int) $project->id,
                    'nama_proyek' => $project->nama_proyek,
                    'deadline' => $deadline->toDateString(),
                    'sisa_hari' => CarbonImmutable::now()->diffInDays($deadline, false),
                    'status' => $project->status,
                ];
            })
            ->all();
    }

    private function buildRecentTasks(): array
    {
        return LaporanAktivitas::query()
            ->with('user:id,name')
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(fn (LaporanAktivitas $task): array => [
                'id' => (int) $task->id,
                'judul' => $task->judul,
                'status' => $task->status,
                'tanggal_aktivitas' => optional($task->tanggal_aktivitas)->toDateString(),
                'is_priority' => (bool) $task->is_priority,
                'user' => [
                    'id' => $task->user?->id,
                    'name' => $task->user?->name,
                ],
            ])
            ->all();
    }
}
