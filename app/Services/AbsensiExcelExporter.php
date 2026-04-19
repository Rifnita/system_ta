<?php

namespace App\Services;

use Illuminate\Support\Carbon;
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
    * @param Collection<int, array<string, mixed>> $records
     * @param array<string, mixed> $meta
     */
    public function download(Collection $records, array $meta, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ringkasan Manager');

        $this->buildHeader($sheet, $meta);
        $lastRecapRow = $this->buildRecap($sheet, $meta);
        $this->buildUserDateRecapTable($sheet, $records, $meta, $lastRecapRow + 2);

        $detailSheet = $spreadsheet->createSheet();
        $detailSheet->setTitle('Detail Absensi');
        $this->buildDetailHeader($detailSheet, $meta);
        $this->buildDetailTable($detailSheet, $records, 7);

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
        $sheet->setCellValue('A6', 'Dicetak Oleh');
        $sheet->setCellValue('B6', (string) ($meta['dicetak_oleh'] ?? '-'));
        $sheet->setCellValue('A7', 'Dicetak Pada');
        $sheet->setCellValue('B7', isset($meta['dicetak_pada']) ? (string) $meta['dicetak_pada'] : '-');

        $sheet->setCellValue('D4', 'Legenda Warna');
        $sheet->mergeCells('D4:F4');
        $sheet->setCellValue('D5', 'Tanggal Merah');
        $sheet->setCellValue('D6', 'Cuti');
        $sheet->setCellValue('D7', 'Catatan');
        $sheet->setCellValue('E7', 'Jika Cuti & Tanggal Merah bersamaan, warna Cuti diprioritaskan.');
        $sheet->mergeCells('E7:F7');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4:A7')->getFont()->setBold(true);
        $sheet->getStyle('D4:F4')->getFont()->setBold(true);
        $sheet->getStyle('D4:F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D4:F7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('E5:F5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF0F0');
        $sheet->getStyle('E6:F6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF9E6');
        $sheet->getStyle('E5:F6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('E5', '');
        $sheet->setCellValue('E6', '');
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
        $sheet->setCellValue('A' . $row, 'Total Hadir');
        $sheet->setCellValue('B' . $row, (int) ($meta['total_hadir'] ?? 0));
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Cuti');
        $sheet->setCellValue('B' . $row, (int) ($meta['total_cuti'] ?? 0));
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Tanggal Merah');
        $sheet->setCellValue('B' . $row, (int) ($meta['total_tanggal_merah'] ?? 0));
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Tidak Absen (Hari Kerja)');
        $sheet->setCellValue('B' . $row, (int) ($meta['total_tidak_absen'] ?? 0));
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

        $sheet->getStyle('A12:B' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B13:B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return $row;
    }

    /**
     * @param array<string, mixed> $meta
     */
    private function buildDetailHeader($sheet, array $meta): void
    {
        $sheet->mergeCells('A1:X1');
        $sheet->mergeCells('A2:X2');

        $sheet->setCellValue('A1', (string) ($meta['company_name'] ?? config('app.name', 'System TA')));
        $sheet->setCellValue('A2', 'Detail Absensi Harian');

        $sheet->setCellValue('A4', 'Periode');
        $sheet->setCellValue('B4', (string) ($meta['periode'] ?? '-'));
        $sheet->setCellValue('A5', 'Pegawai');
        $sheet->setCellValue('B5', (string) ($meta['pegawai'] ?? '-'));

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4:A5')->getFont()->setBold(true);
    }

    /**
     * @param Collection<int, array<string, mixed>> $records
     */
    private function buildUserDateRecapTable($sheet, Collection $records, array $meta, int $headerRow): void
    {
        $sheet->setCellValue('A' . ($headerRow - 1), 'REKAP PER USER (RANGE TANGGAL)');
        $sheet->mergeCells('A' . ($headerRow - 1) . ':D' . ($headerRow - 1));
        $sheet->getStyle('A' . ($headerRow - 1) . ':D' . ($headerRow - 1))->getFont()->setBold(true);

        $startDate = Carbon::parse((string) ($meta['start_date'] ?? now()->toDateString()))->startOfDay();
        $endDate = Carbon::parse((string) ($meta['end_date'] ?? now()->toDateString()))->startOfDay();

        $dateKeys = [];
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $dateKeys[] = $cursor->copy();
            $cursor->addDay();
        }

        $headers = [
            'No',
            'Nama Pegawai',
            'Username',
        ];

        foreach ($dateKeys as $date) {
            $headers[] = $date->format('d/m');
        }

        foreach ($headers as $index => $header) {
            $column = $this->columnByIndex($index + 1);
            $sheet->setCellValue($column . $headerRow, $header);
        }

        $lastColumn = $this->columnByIndex(count($headers));
        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F4E78');
        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $recordsByUser = [];
        foreach ($records as $record) {
            $userKey = (string) (($record['nama_pegawai'] ?? '-') . '|' . ($record['username'] ?? '-'));
            $date = Carbon::createFromFormat('d/m/Y', (string) ($record['tanggal'] ?? now()->format('d/m/Y')))->format('Y-m-d');
            $recordsByUser[$userKey]['nama'] = (string) ($record['nama_pegawai'] ?? '-');
            $recordsByUser[$userKey]['username'] = (string) ($record['username'] ?? '-');
            $recordsByUser[$userKey]['dates'][$date] = [
                'status_kehadiran' => (string) ($record['status_kehadiran'] ?? '-'),
                'kategori_hari' => (string) ($record['kategori_hari'] ?? '-'),
            ];
        }

        $row = $headerRow + 1;
        $no = 1;

        foreach ($recordsByUser as $userData) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, (string) ($userData['nama'] ?? '-'));
            $sheet->setCellValue('C' . $row, (string) ($userData['username'] ?? '-'));

            foreach ($dateKeys as $idx => $date) {
                $dateColumn = $this->columnByIndex(4 + $idx);
                $dateKey = $date->format('Y-m-d');
                $status = $userData['dates'][$dateKey]['status_kehadiran'] ?? '-';
                $kategori = $userData['dates'][$dateKey]['kategori_hari'] ?? '-';

                $cellValue = '-';
                if ($status === 'Hadir') {
                    $cellValue = 'H';
                } elseif ($status === 'Cuti') {
                    $cellValue = 'C';
                } elseif ($kategori === 'Tanggal Merah') {
                    $cellValue = 'TM';
                }

                $sheet->setCellValue($dateColumn . $row, $cellValue);
                $sheet->getStyle($dateColumn . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                if ($kategori === 'Tanggal Merah') {
                    $sheet->getStyle($dateColumn . $row)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFFF0F0');
                }

                if ($status === 'Cuti') {
                    $sheet->getStyle($dateColumn . $row)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFFF9E6');
                }
            }

            $row++;
        }

        if ($row > $headerRow + 1) {
            $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . ($row - 1))
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . ($headerRow + 1) . ':A' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . ($headerRow + 1) . ':C' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(26);
        $sheet->getColumnDimension('C')->setWidth(18);

        for ($index = 4; $index <= count($headers); $index++) {
            $column = $this->columnByIndex($index);
            $sheet->getColumnDimension($column)->setWidth(8);
        }

        foreach (['A', 'B', 'C'] as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    /**
     * @param Collection<int, array<string, mixed>> $records
     */
    private function buildDetailTable($sheet, Collection $records, int $headerRow): void
    {
        $headers = [
            'No',
            'Tanggal',
            'Hari',
            'Nama Pegawai',
            'Username',
            'Kategori Hari',
            'Status Kehadiran',
            'Catatan Sistem',
            'Jenis Cuti',
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

        $row = $headerRow + 1;
        $no = 1;

        foreach ($records as $record) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, (string) ($record['tanggal'] ?? '-'));
            $sheet->setCellValue('C' . $row, (string) ($record['hari'] ?? '-'));
            $sheet->setCellValue('D' . $row, (string) ($record['nama_pegawai'] ?? '-'));
            $sheet->setCellValue('E' . $row, (string) ($record['username'] ?? '-'));
            $sheet->setCellValue('F' . $row, (string) ($record['kategori_hari'] ?? '-'));
            $sheet->setCellValue('G' . $row, (string) ($record['status_kehadiran'] ?? '-'));
            $sheet->setCellValue('H' . $row, (string) ($record['catatan_sistem'] ?? '-'));
            $sheet->setCellValue('I' . $row, (string) ($record['jenis_cuti'] ?? '-'));
            $sheet->setCellValue('J' . $row, (string) ($record['jam_masuk'] ?? '-'));
            $sheet->setCellValue('K' . $row, (string) ($record['jam_keluar'] ?? '-'));
            $sheet->setCellValue('L' . $row, $record['total_jam'] ?? null);
            $sheet->setCellValue('M' . $row, $record['keterlambatan_menit'] ?? null);
            $sheet->setCellValue('N' . $row, (string) ($record['koordinat_masuk'] ?? '-'));
            $sheet->setCellValue('O' . $row, (string) ($record['koordinat_keluar'] ?? '-'));
            $sheet->setCellValue('P' . $row, $record['akurasi_gps_masuk'] ?? null);
            $sheet->setCellValue('Q' . $row, $record['akurasi_gps_keluar'] ?? null);
            $sheet->setCellValue('R' . $row, (string) ($record['fake_gps_masuk'] ?? '-'));
            $sheet->setCellValue('S' . $row, (string) ($record['fake_gps_keluar'] ?? '-'));
            $sheet->setCellValue('T' . $row, (string) ($record['ip_masuk'] ?? '-'));
            $sheet->setCellValue('U' . $row, (string) ($record['ip_keluar'] ?? '-'));
            $sheet->setCellValue('V' . $row, (string) ($record['device_masuk'] ?? '-'));
            $sheet->setCellValue('W' . $row, (string) ($record['device_keluar'] ?? '-'));
            $sheet->setCellValue('X' . $row, (string) ($record['keterangan'] ?? '-'));

            if (($record['kategori_hari'] ?? null) === 'Tanggal Merah') {
                $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFFFF0F0');
            }

            if (($record['status_kehadiran'] ?? null) === 'Cuti') {
                $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFFFF9E6');
            }

            $row++;
        }

        if ($row > $headerRow + 1) {
            $sheet->getStyle('L' . ($headerRow + 1) . ':Q' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . ($row - 1))
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . ($headerRow + 1) . ':A' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . ($headerRow + 1) . ':B' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . ($headerRow + 1) . ':C' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . ($headerRow + 1) . ':K' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', 'X') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
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
