<?php

namespace App\Filament\Widgets;

use App\Models\LaporanMingguan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProgressTrendChartWidget extends ChartWidget
{
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return 'Trend Progress Per Minggu';
    }

    public ?int $proyekId = null;
    public ?int $tahun = null;
    public ?string $tanggalMulai = null;
    public ?string $tanggalAkhir = null;

    protected function getData(): array
    {
        $query = LaporanMingguan::query()
            ->select(
                DB::raw('CONCAT("Minggu ", minggu_ke, " - ", tahun) as periode'),
                DB::raw('AVG(persentase_penyelesaian) as avg_progress'),
                DB::raw('AVG(realisasi_mingguan) as avg_realisasi'),
                'tahun',
                'minggu_ke'
            );

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

        $trendData = $query->groupBy('tahun', 'minggu_ke', 'periode')
            ->orderBy('tahun')
            ->orderBy('minggu_ke')
            ->limit(12)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Progress (%)',
                    'data' => $trendData->pluck('avg_progress')->map(fn($v) => round($v, 2))->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Rata-rata Realisasi (%)',
                    'data' => $trendData->pluck('avg_realisasi')->map(fn($v) => round($v, 2))->toArray(),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => $trendData->pluck('periode')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'ticks' => [
                        'callback' => 'function(value) { return value + "%"; }',
                    ],
                ],
            ],
        ];
    }
}
