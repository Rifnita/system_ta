<?php

namespace App\Http\Controllers;

use App\Models\LaporanAktivitas;
use App\Models\User;
use App\Services\LaporanAktivitasPdfExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class LaporanAktivitasExportController extends Controller
{
    public function exportPdf(Request $request, LaporanAktivitasPdfExporter $exporter)
    {
        $data = $request->validate([
            'periode' => ['required', 'in:harian,mingguan,bulanan'],
            'tanggal' => ['nullable', 'date'],
            'bulan' => ['nullable', 'integer', 'min:1', 'max:12'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $periode = (string) $data['periode'];

        $startDate = null;
        $endDate = null;
        $periodeLabel = '-';

        if (in_array($periode, ['harian', 'mingguan'], true)) {
            $tanggal = Carbon::parse($data['tanggal'] ?? now());

            if ($periode === 'harian') {
                $startDate = $tanggal->toDateString();
                $endDate = $tanggal->toDateString();
                $periodeLabel = 'Harian (' . $tanggal->translatedFormat('d M Y') . ')';
            } else {
                $mulai = $tanggal->copy()->startOfWeek(Carbon::MONDAY);
                $sampai = $tanggal->copy()->endOfWeek(Carbon::SUNDAY);
                $startDate = $mulai->toDateString();
                $endDate = $sampai->toDateString();
                $periodeLabel = 'Mingguan (' . $mulai->translatedFormat('d M Y') . ' - ' . $sampai->translatedFormat('d M Y') . ')';
            }
        }

        if ($periode === 'bulanan') {
            $bulan = (int) ($data['bulan'] ?? now()->month);
            $tahun = (int) ($data['tahun'] ?? now()->year);
            $mulai = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $sampai = Carbon::create($tahun, $bulan, 1)->endOfMonth();
            $startDate = $mulai->toDateString();
            $endDate = $sampai->toDateString();
            $periodeLabel = 'Bulanan (' . $mulai->translatedFormat('F Y') . ')';
        }

        abort_if(! $startDate || ! $endDate, 422, 'Periode tidak valid.');

        $query = LaporanAktivitas::query()
            ->with('user')
            ->whereBetween('tanggal_aktivitas', [$startDate, $endDate])
            ->orderBy('tanggal_aktivitas')
            ->orderBy('waktu_mulai');

        if (Auth::user()?->can('view_any_laporan::aktivitas') !== true) {
            $query->where('user_id', Auth::id());
        } elseif (! empty($data['user_id'])) {
            $query->where('user_id', (int) $data['user_id']);
        }

        $records = $query->get();

        $pegawai = 'Semua Pegawai';
        if (Auth::user()?->can('view_any_laporan::aktivitas') !== true) {
            $pegawai = Auth::user()?->name ?? '-';
        } elseif (! empty($data['user_id'])) {
            $pegawai = User::find((int) $data['user_id'])?->name ?? '-';
        }

        $filename = 'laporan-aktivitas-' . $startDate . '-sd-' . $endDate;
        if ($pegawai !== 'Semua Pegawai') {
            $filename .= '-' . $pegawai;
        }
        $filename .= '.pdf';

        return $exporter->download(
            records: $records,
            meta: [
                'judul' => 'Laporan Aktivitas',
                'periode' => $periodeLabel,
                'pegawai' => $pegawai,
            ],
            filename: $filename,
        );
    }

    public function exportSinglePdf(LaporanAktivitas $laporanAktivitas, LaporanAktivitasPdfExporter $exporter)
    {
        $laporanAktivitas->loadMissing('user');

        if (Auth::user()?->can('view_any_laporan::aktivitas') !== true && $laporanAktivitas->user_id !== Auth::id()) {
            abort(403);
        }

        $tanggalCarbon = $laporanAktivitas->tanggal_aktivitas
            ? Carbon::parse($laporanAktivitas->tanggal_aktivitas)
            : null;

        $tanggal = $tanggalCarbon?->translatedFormat('d M Y') ?? '-';
        $pegawai = $laporanAktivitas->user?->name ?? '-';

        $filename = 'laporan-aktivitas-' . ($tanggalCarbon?->format('Y-m-d') ?? now()->format('Y-m-d')) . '-' . $pegawai . '.pdf';

        return $exporter->download(
            records: collect([$laporanAktivitas]),
            meta: [
                'judul' => 'Laporan Aktivitas',
                'periode' => 'Per Aktivitas (' . $tanggal . ')',
                'pegawai' => $pegawai,
            ],
            filename: $filename,
        );
    }
}
