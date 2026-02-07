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
        // Analytics widget - visible for managers
        $user = Auth::user();
        return $user && $user->hasAnyRole(['super_admin', 'panel_user']);
    }

    public function getHeading(): ?string
    {
        return 'Projects Distribution By Status';
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
                    'label' => 'Projects',
                    'data' => [$perencanaan, $dalamPengerjaan, $tertunda, $selesai],
                    'backgroundColor' => [
                        'rgba(147, 197, 253, 0.5)',  // light blue - perencanaan
                        'rgba(59, 130, 246, 0.5)',   // blue - dalam pengerjaan
                        'rgba(251, 191, 36, 0.5)',   // yellow - tertunda
                        'rgba(34, 197, 94, 0.5)',    // green - selesai
                    ],
                    'borderColor' => [
                        'rgb(147, 197, 253)',
                        'rgb(59, 130, 246)',
                        'rgb(251, 191, 36)',
                        'rgb(34, 197, 94)',
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
