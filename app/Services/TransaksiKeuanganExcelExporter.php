<?php

namespace App\Services;

use App\Models\TransaksiKeuangan;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TransaksiKeuanganExcelExporter
{
    /**
     * @param Collection<int, TransaksiKeuangan> $records
     * @param array<string, mixed> $meta
     */
    public function download(Collection $records, array $meta, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Transaksi Keuangan');

        $sheet->setCellValue('A1', $meta['judul'] ?? 'Laporan Transaksi Keuangan');
        $sheet->setCellValue('A2', 'Periode');
        $sheet->setCellValue('B2', $meta['periode'] ?? '-');
        $sheet->setCellValue('A3', 'Pegawai');
        $sheet->setCellValue('B3', $meta['pegawai'] ?? '-');
        $sheet->setCellValue('A4', 'Dicetak Oleh');
        $sheet->setCellValue('B4', $meta['dicetak_oleh'] ?? '-');
        $sheet->setCellValue('A5', 'Dicetak Pada');
        $sheet->setCellValue('B5', isset($meta['dicetak_pada']) ? (string) $meta['dicetak_pada'] : '-');

        $headers = ['Tanggal', 'Jenis', 'Kategori', 'Nominal', 'Metode', 'Proyek', 'Status', 'Pencatat', 'Referensi', 'Deskripsi'];
        $headerRow = 7;

        foreach ($headers as $index => $header) {
            $column = chr(65 + $index);
            $sheet->setCellValue($column . $headerRow, $header);
        }

        $row = $headerRow + 1;

        foreach ($records as $record) {
            $sheet->setCellValue('A' . $row, optional($record->tanggal)->format('d/m/Y'));
            $sheet->setCellValue('B' . $row, $record->jenis === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran');
            $sheet->setCellValue('C' . $row, $record->kategori?->nama ?? '-');
            $sheet->setCellValue('D' . $row, (float) $record->nominal);
            $sheet->setCellValue('E' . $row, $this->formatMetode((string) $record->metode_pembayaran));
            $sheet->setCellValue('F' . $row, $record->proyek?->nama_proyek ?? '-');
            $sheet->setCellValue('G' . $row, $record->status === 'draft' ? 'Draft' : 'Tercatat');
            $sheet->setCellValue('H' . $row, $record->user?->name ?? '-');
            $sheet->setCellValueExplicit('I' . $row, (string) ($record->nomor_referensi ?? '-'), DataType::TYPE_STRING);
            $sheet->setCellValue('J' . $row, (string) ($record->deskripsi ?? '-'));
            $row++;
        }

        $summaryStart = $row + 1;
        $sheet->setCellValue('A' . $summaryStart, 'Ringkasan');
        $sheet->setCellValue('A' . ($summaryStart + 1), 'Total Pemasukan');
        $sheet->setCellValue('B' . ($summaryStart + 1), (float) ($meta['total_pemasukan'] ?? 0));
        $sheet->setCellValue('A' . ($summaryStart + 2), 'Total Pengeluaran');
        $sheet->setCellValue('B' . ($summaryStart + 2), (float) ($meta['total_pengeluaran'] ?? 0));
        $sheet->setCellValue('A' . ($summaryStart + 3), 'Saldo');
        $sheet->setCellValue('B' . ($summaryStart + 3), (float) ($meta['saldo'] ?? 0));

        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':J' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('D' . ($headerRow + 1) . ':D' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('B' . ($summaryStart + 1) . ':B' . ($summaryStart + 3))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A1:J1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

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

    private function formatMetode(string $metode): string
    {
        return match ($metode) {
            'transfer_bank' => 'Transfer Bank',
            'e_wallet' => 'E-Wallet',
            'kartu_debit' => 'Kartu Debit',
            'kartu_kredit' => 'Kartu Kredit',
            default => ucfirst(str_replace('_', ' ', $metode)),
        };
    }
}
