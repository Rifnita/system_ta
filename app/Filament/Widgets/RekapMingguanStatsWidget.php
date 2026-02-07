<?php

namespace App\Filament\Widgets;

use App\Models\LaporanMingguan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class RekapMingguanStatsWidget extends BaseWidget
{
    public ?int $proyekId = null;
    public ?int $tahun = null;
    public ?string $tanggalMulai = null;
    public ?string $tanggalAkhir = null;

    protected function getStats(): array
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

        $totalLaporan = $query->count();
        $avgProgress = $query->avg('persentase_penyelesaian') ?? 0;
        $targetTercapai = $query->whereRaw('realisasi_mingguan >= target_mingguan')->count();
        $totalProyek = $query->distinct('proyek_id')->count('proyek_id');
        
        $targetPercentage = $totalLaporan > 0 ? round(($targetTercapai / $totalLaporan) * 100, 2) : 0;

        return [
            Stat::make('Total Laporan Mingguan', $totalLaporan)
                ->description('Jumlah laporan masuk')
                ->color('primary'),
            
            Stat::make('Rata-rata Progress', number_format($avgProgress, 2) . '%')
                ->description('Progress keseluruhan')
                ->color('success'),
            
            Stat::make('Target Tercapai', $targetTercapai . ' dari ' . $totalLaporan)
                ->description($targetPercentage . '% tercapai')
                ->color($targetPercentage >= 70 ? 'success' : ($targetPercentage >= 50 ? 'warning' : 'danger')),
            
            Stat::make('Proyek Dilaporkan', $totalProyek)
                ->description('Proyek aktif')
                ->color('info'),
        ];
    }

    protected function getWeeklyChart(): array
    {
        $query = LaporanMingguan::query()
            ->select(DB::raw('CONCAT(tahun, "-W", LPAD(minggu_ke, 2, "0")) as week'), DB::raw('count(*) as total'))
            ->groupBy('week')
            ->orderBy('week', 'desc')
            ->limit(7);

        if ($this->proyekId) {
            $query->where('proyek_id', $this->proyekId);
        }

        if ($this->tahun) {
            $query->where('tahun', $this->tahun);
        }

        return $query->pluck('total')->reverse()->toArray();
    }
}
