<?php

namespace App\Filament\Widgets;

use App\Models\LaporanMingguan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KualitasLaporanChartWidget extends ChartWidget
{
    protected static ?int $sort = 6;
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
        return 'Distribusi Status Kualitas';
    }

    public ?int $proyekId = null;
    public ?int $tahun = null;
    public ?string $tanggalMulai = null;
    public ?string $tanggalAkhir = null;

    protected function getData(): array
    {
        $query = LaporanMingguan::query();

        if ($this->proyekId) {
            $query->where('proyek_id', $this->proyekId);
        }

        if ($this->tahun) {
            $query->where('tahun', $this->tahun);
        }

        if ($this->tanggalMulai) {
            $query->whereDate('tanggal_mulai', '>=', $this->tanggalMulai);
        }

        if ($this->tanggalAkhir) {
            $query->whereDate('tanggal_akhir', '<=', $this->tanggalAkhir);
        }

        $qualityData = $query->select('status_kualitas', DB::raw('count(*) as total'))
            ->whereNotNull('status_kualitas')
            ->groupBy('status_kualitas')
            ->pluck('total', 'status_kualitas')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Laporan',
                    'data' => [
                        $qualityData['excellent'] ?? 0,
                        $qualityData['good'] ?? 0,
                        $qualityData['fair'] ?? 0,
                        $qualityData['poor'] ?? 0,
                    ],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.5)',   // green - excellent
                        'rgba(59, 130, 246, 0.5)',  // blue - good
                        'rgba(251, 191, 36, 0.5)',  // yellow - fair
                        'rgba(239, 68, 68, 0.5)',   // red - poor
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',
                        'rgb(251, 191, 36)',
                        'rgb(239, 68, 68)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Sangat Baik', 'Baik', 'Cukup', 'Buruk'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
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
            'maintainAspectRatio' => false,
        ];
    }
}
