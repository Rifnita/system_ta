<?php

namespace App\Filament\Widgets;

use App\Models\LaporanAktivitas;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkloadDistributionWidget extends ChartWidget
{
    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 'full';
    protected ?string $maxHeight = '350px';

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user && $user->hasAnyRole(['super_admin', 'panel_user']);
    }

    public function getHeading(): ?string
    {
        return 'Distribusi Beban Kerja Tim (Tugas Aktif)';
    }

    public function getDescription(): ?string
    {
        $totalActiveTasks = LaporanAktivitas::whereIn('status', ['pending', 'in_progress'])->count();
        $activeUsers = User::where('is_active', true)->count();

        return "Total tugas aktif: $totalActiveTasks pada $activeUsers anggota tim";
    }

    protected function getData(): array
    {
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
            $userName = $data->user->name ?? 'Pengguna tidak diketahui';
            $userNames[] = strlen($userName) > 20 ? substr($userName, 0, 17) . '...' : $userName;
            $taskCounts[] = $data->task_count;

            if ($data->task_count > 20) {
                $backgroundColors[] = 'rgba(225, 29, 72, 0.65)';
            } elseif ($data->task_count > 10) {
                $backgroundColors[] = 'rgba(208, 173, 99, 0.75)';
            } elseif ($data->task_count > 5) {
                $backgroundColors[] = 'rgba(64, 91, 151, 0.72)';
            } else {
                $backgroundColors[] = 'rgba(22, 163, 74, 0.65)';
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Tugas Aktif',
                    'data' => $taskCounts,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => array_map(function ($color) {
                        return str_replace(['0.65', '0.72', '0.75'], '1', $color);
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
            'indexAxis' => 'y',
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
                        'text' => 'Jumlah Tugas Aktif',
                    ],
                    'ticks' => [
                        'stepSize' => 5,
                    ],
                ],
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Anggota Tim',
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
