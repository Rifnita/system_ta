<?php

namespace App\Services;

use App\Models\TransaksiKeuangan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class TransaksiKeuanganPdfExporter
{
    /**
     * @param Collection<int, TransaksiKeuangan> $records
     * @param array<string, mixed> $meta
     */
    public function download(Collection $records, array $meta, string $filename)
    {
        $tempDir = storage_path('app/mpdf-temp');
        if (! File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $html = view('pdf.transaksi-keuangan', [
            'records' => $records,
            'meta' => $meta,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'L',
            'tempDir' => $tempDir,
            'default_font' => 'dejavusans',
            'default_font_size' => 9,
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 12,
            'margin_bottom' => 12,
        ]);

        $mpdf->SetTitle($meta['judul'] ?? 'Laporan Transaksi Keuangan');
        $mpdf->WriteHTML($html);

        $safeFilename = Str::of($filename)
            ->replace(['/', '\\'], '-')
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9._-]+/', '-')
            ->trim('-')
            ->toString();

        if (! str_ends_with(strtolower($safeFilename), '.pdf')) {
            $safeFilename .= '.pdf';
        }

        $pdfContent = $mpdf->Output($safeFilename, Destination::STRING_RETURN);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $safeFilename . '"',
        ]);
    }
}
