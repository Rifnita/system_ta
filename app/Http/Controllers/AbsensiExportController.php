<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use App\Services\AbsensiExcelExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AbsensiExportController extends Controller
{
    public function exportExcel(Request $request, AbsensiExcelExporter $exporter)
    {
        $this->authorizeExport();

        [$records, $meta] = $this->collectRecords($request);

        $filename = 'rekap-absensi-' . Carbon::parse($meta['start_date'])->format('Y-m-d')
            . '-sd-' . Carbon::parse($meta['end_date'])->format('Y-m-d') . '.xlsx';

        return $exporter->download($records, $meta, $filename);
    }

    private function collectRecords(Request $request): array
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:hadir,izin,sakit,cuti,alpha,dinas_luar,lembur'],
            'status_persetujuan' => ['nullable', 'in:menunggu,disetujui,ditolak'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $startDate = Carbon::parse($data['start_date'])->toDateString();
        $endDate = Carbon::parse($data['end_date'])->toDateString();

        $query = Absensi::query()
            ->with(['user', 'approver'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->orderBy('jam_masuk');

        if (! $this->canViewAll()) {
            $query->where('user_id', Auth::id());
        } elseif (! empty($data['user_id'])) {
            $query->where('user_id', (int) $data['user_id']);
        }

        if (! empty($data['status'])) {
            $query->where('status', $data['status']);
        }

        if (! empty($data['status_persetujuan'])) {
            $query->where('status_persetujuan', $data['status_persetujuan']);
        }

        $records = $query->get();

        $pegawai = 'Semua Pegawai';
        if (! $this->canViewAll()) {
            $pegawai = Auth::user()?->name ?? '-';
        } elseif (! empty($data['user_id'])) {
            $pegawai = User::find((int) $data['user_id'])?->name ?? '-';
        }

        $statusCounts = [
            'hadir' => (int) $records->where('status', 'hadir')->count(),
            'izin' => (int) $records->where('status', 'izin')->count(),
            'sakit' => (int) $records->where('status', 'sakit')->count(),
            'cuti' => (int) $records->where('status', 'cuti')->count(),
            'alpha' => (int) $records->where('status', 'alpha')->count(),
            'dinas_luar' => (int) $records->where('status', 'dinas_luar')->count(),
            'lembur' => (int) $records->where('status', 'lembur')->count(),
        ];

        $meta = [
            'judul' => 'Rekap Absensi Karyawan',
            'company_name' => config('app.name', 'System TA'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'periode' => Carbon::parse($startDate)->translatedFormat('d M Y') . ' - ' . Carbon::parse($endDate)->translatedFormat('d M Y'),
            'pegawai' => $pegawai,
            'status_filter' => $data['status'] ?? null,
            'status_persetujuan_filter' => $data['status_persetujuan'] ?? null,
            'dicetak_oleh' => Auth::user()?->name,
            'dicetak_pada' => now(),
            'total_data' => $records->count(),
            'total_jam_kerja' => (float) $records->sum(fn (Absensi $item) => (float) ($item->total_jam_kerja ?? 0)),
            'total_keterlambatan_menit' => (int) $records->sum(fn (Absensi $item) => (int) ($item->keterlambatan_menit ?? 0)),
            'total_fake_gps_masuk' => (int) $records->where('mock_location_detected_masuk', true)->count(),
            'total_fake_gps_keluar' => (int) $records->where('mock_location_detected_keluar', true)->count(),
            'status_counts' => $statusCounts,
        ];

        return [$records, $meta];
    }

    private function canViewAll(): bool
    {
        $user = Auth::user();

        return (bool) ($user && method_exists($user, 'hasRole')
            && ($user->hasRole('super_admin') || $user->hasRole('admin')));
    }

    private function authorizeExport(): void
    {
        abort_unless(Auth::check(), 403);
    }
}
