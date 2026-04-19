<?php

namespace App\Services;

use App\Models\Absensi;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AbsensiExcelExporter
{
    /**
     * @param Collection<int, Absensi> $records
     * @param array<string, mixed> $meta
     */
    public function download(Collection $records, array $meta, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Absensi');

        $this->buildHeader($sheet, $meta);
        $lastRecapRow = $this->buildRecap($sheet, $meta);
        $this->buildDetailTable($sheet, $records, $lastRecapRow + 2);

        $safeFilename = Str::of($filename)
            ->replace(['/', '\\'], '-')
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9._-]+/', '-')
            ->trim('-')
            ->toString();

        if (! str_ends_with(strtolower($safeFilename), '.xlsx')) {
            $safeFilename .= '.xlsx';
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $safeFilename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * @param array<string, mixed> $meta
     */
    private function buildHeader($sheet, array $meta): void
    {
        $sheet->mergeCells('A1:W1');
        $sheet->mergeCells('A2:W2');

        $sheet->setCellValue('A1', (string) ($meta['company_name'] ?? config('app.name', 'System TA')));
        $sheet->setCellValue('A2', (string) ($meta['judul'] ?? 'Rekap Absensi Karyawan'));

        $sheet->setCellValue('A4', 'Periode Laporan');
        $sheet->setCellValue('B4', (string) ($meta['periode'] ?? '-'));
        $sheet->setCellValue('A5', 'Pegawai');
        $sheet->setCellValue('B5', (string) ($meta['pegawai'] ?? '-'));
        $sheet->setCellValue('A6', 'Status');
        $sheet->setCellValue('B6', $this->labelStatus($meta['status_filter'] ?? null));
        $sheet->setCellValue('A7', 'Persetujuan');
        $sheet->setCellValue('B7', $this->labelStatusPersetujuan($meta['status_persetujuan_filter'] ?? null));
        $sheet->setCellValue('A8', 'Dicetak Oleh');
        $sheet->setCellValue('B8', (string) ($meta['dicetak_oleh'] ?? '-'));
        $sheet->setCellValue('A9', 'Dicetak Pada');
        $sheet->setCellValue('B9', isset($meta['dicetak_pada']) ? (string) $meta['dicetak_pada'] : '-');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4:A9')->getFont()->setBold(true);
    }

    /**
     * @param array<string, mixed> $meta
     */
    private function buildRecap($sheet, array $meta): int
    {
        $row = 11;
        $sheet->setCellValue('A' . $row, 'REKAPITULASI');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDCE6F1');

        $row++;
        $sheet->setCellValue('A' . $row, 'Total Data Absensi');
        $sheet->setCellValue('B' . $row, (int) ($meta['total_data'] ?? 0));
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Jam Kerja');
        $sheet->setCellValue('B' . $row, (float) ($meta['total_jam_kerja'] ?? 0));
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Keterlambatan (menit)');
        $sheet->setCellValue('B' . $row, (int) ($meta['total_keterlambatan_menit'] ?? 0));
        $row++;
        $sheet->setCellValue('A' . $row, 'Fake GPS Masuk');
        $sheet->setCellValue('B' . $row, (int) ($meta['total_fake_gps_masuk'] ?? 0));
        $row++;
        $sheet->setCellValue('A' . $row, 'Fake GPS Keluar');
        $sheet->setCellValue('B' . $row, (int) ($meta['total_fake_gps_keluar'] ?? 0));

        $statusCounts = $meta['status_counts'] ?? [];
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Distribusi Status');
        $sheet->setCellValue('B' . $row, 'Jumlah');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');

        foreach (['hadir', 'izin', 'sakit', 'cuti', 'alpha', 'dinas_luar', 'lembur'] as $statusKey) {
            $row++;
            $sheet->setCellValue('A' . $row, $this->labelStatus($statusKey));
            $sheet->setCellValue('B' . $row, (int) ($statusCounts[$statusKey] ?? 0));
        }

        $sheet->getStyle('A12:B' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B13:B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return $row;
    }

    /**
     * @param Collection<int, Absensi> $records
     */
    private function buildDetailTable($sheet, Collection $records, int $headerRow): void
    {
        $headers = [
            'No',
            'Tanggal',
            'Nama Pegawai',
            'Username',
            'Status',
            'Status Persetujuan',
            'Jam Masuk',
            'Jam Keluar',
            'Total Jam',
            'Terlambat (Menit)',
            'Koordinat Masuk',
            'Koordinat Keluar',
            'Akurasi GPS Masuk',
            'Akurasi GPS Keluar',
            'Fake GPS Masuk',
            'Fake GPS Keluar',
            'IP Masuk',
            'IP Keluar',
            'Device Masuk',
            'Device Keluar',
            'Disetujui Oleh',
            'Waktu Persetujuan',
            'Keterangan',
        ];

        foreach ($headers as $index => $header) {
            $column = $this->columnByIndex($index + 1);
            $sheet->setCellValue($column . $headerRow, $header);
        }

        $lastColumn = $this->columnByIndex(count($headers));
        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F4E78');
        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->freezePane('A' . ($headerRow + 1));

        $row = $headerRow + 1;
        $no = 1;

        foreach ($records as $record) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, optional($record->tanggal)->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $record->user?->name ?? '-');
            $sheet->setCellValue('D' . $row, $record->user?->username ?? '-');
            $sheet->setCellValue('E' . $row, $this->labelStatus($record->status));
            $sheet->setCellValue('F' . $row, $this->labelStatusPersetujuan($record->status_persetujuan));
            $sheet->setCellValue('G' . $row, $this->formatTime($record->jam_masuk));
            $sheet->setCellValue('H' . $row, $this->formatTime($record->jam_keluar));
            $sheet->setCellValue('I' . $row, (float) ($record->total_jam_kerja ?? 0));
            $sheet->setCellValue('J' . $row, (int) ($record->keterlambatan_menit ?? 0));
            $sheet->setCellValue('K' . $row, $this->formatCoordinate($record->latitude_masuk, $record->longitude_masuk));
            $sheet->setCellValue('L' . $row, $this->formatCoordinate($record->latitude_keluar, $record->longitude_keluar));
            $sheet->setCellValue('M' . $row, $record->akurasi_gps_masuk !== null ? (float) $record->akurasi_gps_masuk : null);
            $sheet->setCellValue('N' . $row, $record->akurasi_gps_keluar !== null ? (float) $record->akurasi_gps_keluar : null);
            $sheet->setCellValue('O' . $row, $record->mock_location_detected_masuk ? 'Ya' : 'Tidak');
            $sheet->setCellValue('P' . $row, $record->mock_location_detected_keluar ? 'Ya' : 'Tidak');
            $sheet->setCellValue('Q' . $row, $record->ip_address_masuk ?? '-');
            $sheet->setCellValue('R' . $row, $record->ip_address_keluar ?? '-');
            $sheet->setCellValue('S' . $row, $record->device_id_masuk ?? '-');
            $sheet->setCellValue('T' . $row, $record->device_id_keluar ?? '-');
            $sheet->setCellValue('U' . $row, $record->approver?->name ?? '-');
            $sheet->setCellValue('V' . $row, optional($record->approved_at)->format('d/m/Y H:i') ?? '-');
            $sheet->setCellValue('W' . $row, $record->keterangan ?? '-');
            $row++;
        }

        if ($row > $headerRow + 1) {
            $sheet->getStyle('A' . ($headerRow + 1) . ':I' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . ($row - 1))
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . ($headerRow + 1) . ':A' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . ($headerRow + 1) . ':B' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . ($headerRow + 1) . ':H' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', 'W') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function labelStatus(?string $status): string
    {
        return match ($status) {
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'cuti' => 'Cuti',
            'alpha' => 'Alpha',
            'dinas_luar' => 'Dinas Luar',
            'lembur' => 'Lembur',
            null => 'Semua',
            default => Str::headline((string) $status),
        };
    }

    private function labelStatusPersetujuan(?string $status): string
    {
        return match ($status) {
            'menunggu' => 'Menunggu',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            null => 'Semua',
            default => Str::headline((string) $status),
        };
    }

    private function formatCoordinate($latitude, $longitude): string
    {
        if ($latitude === null || $longitude === null) {
            return '-';
        }

        return (string) $latitude . ', ' . (string) $longitude;
    }

    private function formatTime($value): string
    {
        if (blank($value)) {
            return '-';
        }

        try {
            return (string) \Illuminate\Support\Carbon::parse($value)->format('H:i');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function columnByIndex(int $index): string
    {
        $column = '';

        while ($index > 0) {
            $index--;
            $column = chr(65 + ($index % 26)) . $column;
            $index = (int) floor($index / 26);
        }

        return $column;
    }
}
