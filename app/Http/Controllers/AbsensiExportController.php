<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\PengajuanCuti;
use App\Models\User;
use App\Services\AbsensiExcelExporter;
use App\Services\TanggalMerahService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AbsensiExportController extends Controller
{
    public function exportExcel(Request $request, AbsensiExcelExporter $exporter, TanggalMerahService $tanggalMerahService)
    {
        $this->authorizeExport();

        [$records, $meta] = $this->collectRecords($request, $tanggalMerahService);

        $filename = 'rekap-absensi-' . Carbon::parse($meta['start_date'])->format('Y-m-d')
            . '-sd-' . Carbon::parse($meta['end_date'])->format('Y-m-d') . '.xlsx';

        return $exporter->download($records, $meta, $filename);
    }

    private function collectRecords(Request $request, TanggalMerahService $tanggalMerahService): array
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->startOfDay();
        $dateRange = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateRange[] = $currentDate->copy();
            $currentDate->addDay();
        }

        $userIds = $this->resolveUserIds($data);

        $users = User::query()
            ->whereIn('id', $userIds)
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $absensiRecords = Absensi::query()
            ->with(['user'])
            ->whereIn('user_id', $userIds)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('tanggal')
            ->orderBy('jam_masuk')
            ->get();

        $absensiMap = $absensiRecords->keyBy(fn (Absensi $record): string => $record->user_id . '|' . optional($record->tanggal)->toDateString());

        $cutiRecords = PengajuanCuti::query()
            ->whereIn('user_id', $userIds)
            ->where('status_pengajuan', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $endDate->toDateString())
            ->whereDate('tanggal_selesai', '>=', $startDate->toDateString())
            ->orderBy('tanggal_mulai')
            ->get()
            ->groupBy('user_id');

        $records = collect();
        $totalHadir = 0;
        $totalCuti = 0;
        $totalTanggalMerah = 0;
        $totalTidakAbsen = 0;
        $totalJamKerja = 0.0;
        $totalKeterlambatanMenit = 0;
        $totalFakeGpsMasuk = 0;
        $totalFakeGpsKeluar = 0;

        foreach ($users as $user) {
            foreach ($dateRange as $date) {
                $dateString = $date->toDateString();
                $absensi = $absensiMap->get($user->id . '|' . $dateString);
                $cuti = $this->findApprovedLeaveForDate($cutiRecords->get($user->id, collect()), $dateString);
                $tanggalMerah = $tanggalMerahService->getDateInfo($date);

                if ($tanggalMerah['is_red']) {
                    $totalTanggalMerah++;
                }

                $statusKehadiran = 'Tidak Absen';
                $kategoriHari = $tanggalMerah['is_red'] ? 'Tanggal Merah' : 'Hari Kerja';
                $catatanSistem = $tanggalMerah['label'];

                if ($cuti) {
                    $statusKehadiran = 'Cuti';
                    $kategoriHari = 'Cuti';
                    $catatanSistem = 'Cuti disetujui: ' . $this->labelJenisCuti((string) $cuti->jenis_cuti);
                    $totalCuti++;
                }

                if ($absensi) {
                    $statusKehadiran = 'Hadir';
                    $totalHadir++;
                    $totalJamKerja += (float) ($absensi->total_jam_kerja ?? 0);
                    $totalKeterlambatanMenit += (int) ($absensi->keterlambatan_menit ?? 0);
                    if ($absensi->mock_location_detected_masuk) {
                        $totalFakeGpsMasuk++;
                    }
                    if ($absensi->mock_location_detected_keluar) {
                        $totalFakeGpsKeluar++;
                    }
                }

                if (! $absensi && ! $cuti && ! $tanggalMerah['is_red']) {
                    $totalTidakAbsen++;
                }

                $records->push([
                    'tanggal' => $date->format('d/m/Y'),
                    'hari' => $date->translatedFormat('l'),
                    'nama_pegawai' => $user->name,
                    'username' => $user->username,
                    'kategori_hari' => $kategoriHari,
                    'status_kehadiran' => $statusKehadiran,
                    'catatan_sistem' => $catatanSistem,
                    'jenis_cuti' => $cuti ? $this->labelJenisCuti((string) $cuti->jenis_cuti) : '-',
                    'jam_masuk' => $absensi ? $this->formatTimeValue($absensi->jam_masuk) : '-',
                    'jam_keluar' => $absensi ? $this->formatTimeValue($absensi->jam_keluar) : '-',
                    'total_jam' => $absensi ? (float) ($absensi->total_jam_kerja ?? 0) : null,
                    'keterlambatan_menit' => $absensi ? (int) ($absensi->keterlambatan_menit ?? 0) : null,
                    'koordinat_masuk' => $absensi ? $this->formatCoordinateValue($absensi->latitude_masuk, $absensi->longitude_masuk) : '-',
                    'koordinat_keluar' => $absensi ? $this->formatCoordinateValue($absensi->latitude_keluar, $absensi->longitude_keluar) : '-',
                    'akurasi_gps_masuk' => $absensi && $absensi->akurasi_gps_masuk !== null ? (float) $absensi->akurasi_gps_masuk : null,
                    'akurasi_gps_keluar' => $absensi && $absensi->akurasi_gps_keluar !== null ? (float) $absensi->akurasi_gps_keluar : null,
                    'fake_gps_masuk' => $absensi ? ($absensi->mock_location_detected_masuk ? 'Ya' : 'Tidak') : '-',
                    'fake_gps_keluar' => $absensi ? ($absensi->mock_location_detected_keluar ? 'Ya' : 'Tidak') : '-',
                    'ip_masuk' => $absensi ? ($absensi->ip_address_masuk ?? '-') : '-',
                    'ip_keluar' => $absensi ? ($absensi->ip_address_keluar ?? '-') : '-',
                    'device_masuk' => $absensi ? ($absensi->device_id_masuk ?? '-') : '-',
                    'device_keluar' => $absensi ? ($absensi->device_id_keluar ?? '-') : '-',
                    'keterangan' => $absensi ? ($absensi->keterangan ?? '-') : '-',
                ]);
            }
        }

        $pegawai = 'Semua Pegawai';
        if (! $this->canViewAll()) {
            $pegawai = Auth::user()?->name ?? '-';
        } elseif (! empty($data['user_id'])) {
            $pegawai = User::find((int) $data['user_id'])?->name ?? '-';
        }

        $meta = [
            'judul' => 'Rekap Absensi Karyawan',
            'company_name' => config('app.name', 'System TA'),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'periode' => $startDate->translatedFormat('d M Y') . ' - ' . $endDate->translatedFormat('d M Y'),
            'pegawai' => $pegawai,
            'dicetak_oleh' => Auth::user()?->name,
            'dicetak_pada' => now(),
            'total_data' => $records->count(),
            'total_hadir' => $totalHadir,
            'total_cuti' => $totalCuti,
            'total_tanggal_merah' => $totalTanggalMerah,
            'total_tidak_absen' => $totalTidakAbsen,
            'total_jam_kerja' => $totalJamKerja,
            'total_keterlambatan_menit' => $totalKeterlambatanMenit,
            'total_fake_gps_masuk' => $totalFakeGpsMasuk,
            'total_fake_gps_keluar' => $totalFakeGpsKeluar,
        ];

        return [$records, $meta];
    }

    private function canViewAll(): bool
    {
        return $this->isSuperAdmin();
    }

    private function authorizeExport(): void
    {
        abort_unless($this->isSuperAdmin(), 403);
    }

    private function isSuperAdmin(): bool
    {
        $user = Auth::user();

        return (bool) ($user && method_exists($user, 'hasRole') && $user->hasRole('super_admin'));
    }

    /**
     * @param array<string, mixed> $data
     * @return array<int, int>
     */
    private function resolveUserIds(array $data): array
    {
        if (! $this->canViewAll()) {
            return [Auth::id()];
        }

        if (! empty($data['user_id'])) {
            return [(int) $data['user_id']];
        }

        $userIds = User::query()
            ->orderBy('name')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if (empty($userIds)) {
            $fallbackId = Auth::id();

            return $fallbackId ? [$fallbackId] : [];
        }

        return $userIds;
    }

    /**
     * @param Collection<int, PengajuanCuti> $userLeaves
     */
    private function findApprovedLeaveForDate(Collection $userLeaves, string $date): ?PengajuanCuti
    {
        return $userLeaves->first(function (PengajuanCuti $leave) use ($date): bool {
            return optional($leave->tanggal_mulai)->toDateString() <= $date
                && optional($leave->tanggal_selesai)->toDateString() >= $date;
        });
    }

    private function labelJenisCuti(string $jenis): string
    {
        return match ($jenis) {
            'tahunan' => 'Cuti Tahunan',
            'sakit' => 'Cuti Sakit',
            'melahirkan' => 'Cuti Melahirkan',
            'penting' => 'Cuti Alasan Penting',
            default => 'Cuti Lainnya',
        };
    }

    private function formatTimeValue($value): string
    {
        if (blank($value)) {
            return '-';
        }

        return Carbon::parse($value)->format('H:i');
    }

    private function formatCoordinateValue($latitude, $longitude): string
    {
        if ($latitude === null || $longitude === null) {
            return '-';
        }

        return (string) $latitude . ', ' . (string) $longitude;
    }
}
