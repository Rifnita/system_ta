<?php

namespace App\Filament\Widgets;

use App\Models\Proyek;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectStatusChartWidget extends ChartWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];
    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user && $user->hasAnyRole(['super_admin', 'panel_user']);
    }

    public function getHeading(): ?string
    {
        return 'Distribusi Proyek Berdasarkan Status';
    }

    protected function getData(): array
    {
        $statusData = Proyek::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $perencanaan = $statusData[Proyek::STATUS_PERENCANAAN] ?? 0;
        $dalamPengerjaan = $statusData[Proyek::STATUS_DALAM_PENGERJAAN] ?? 0;
        $tertunda = $statusData[Proyek::STATUS_TERTUNDA] ?? 0;
        $selesai = $statusData[Proyek::STATUS_SELESAI] ?? 0;

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Proyek',
                    'data' => [$perencanaan, $dalamPengerjaan, $tertunda, $selesai],
                    'backgroundColor' => [
                        'rgba(179, 189, 214, 0.65)',
                        'rgba(64, 91, 151, 0.72)',
                        'rgba(208, 173, 99, 0.72)',
                        'rgba(34, 197, 94, 0.55)',
                    ],
                    'borderColor' => [
                        'rgb(140, 157, 193)',
                        'rgb(47, 73, 127)',
                        'rgb(191, 165, 111)',
                        'rgb(22, 163, 74)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Perencanaan', 'Dalam Pengerjaan', 'Tertunda', 'Selesai'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => true,
        ];
    }
}
