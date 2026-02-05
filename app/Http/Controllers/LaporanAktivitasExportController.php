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
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $startDate = Carbon::parse($data['start_date'])->toDateString();
        $endDate = Carbon::parse($data['end_date'])->toDateString();
        
        $startCarbon = Carbon::parse($startDate);
        $endCarbon = Carbon::parse($endDate);
        $periodeLabel = 'Periode ' . $startCarbon->translatedFormat('d M Y') . ' - ' . $endCarbon->translatedFormat('d M Y');

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
