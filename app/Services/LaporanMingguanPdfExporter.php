<?php

namespace App\Services;

use App\Models\LaporanMingguan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class LaporanMingguanPdfExporter
{
    /**
     * Export single laporan mingguan to PDF
     */
    public function download(LaporanMingguan $laporan, string $filename = null)
    {
        $tempDir = storage_path('app/mpdf-temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $meta = [
            'app_name' => config('app.name'),
            'dicetak_oleh' => Auth::user()?->name ?? 'System',
            'dicetak_pada' => now(),
        ];

        $html = view('pdf.laporan-mingguan', [
            'laporan' => $laporan,
            'meta' => $meta,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'tempDir' => $tempDir,
            'default_font' => 'dejavusans',
            'default_font_size' => 10,
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 18,
            'margin_bottom' => 18,
            'showImageErrors' => true,
            'allow_output_buffering' => true,
            'img_dpi' => 96,
        ]);

        // Increase backtrack limit for large HTML with images
        @ini_set('pcre.backtrack_limit', '5000000');

        $title = "Laporan Mingguan - {$laporan->proyek->nama_proyek} - Minggu {$laporan->minggu_ke}";
        $mpdf->SetTitle($title);
        $mpdf->WriteHTML($html);

        // Generate filename if not provided
        if (!$filename) {
            $proyekSlug = Str::slug($laporan->proyek->nama_proyek);
            $filename = "laporan-mingguan-{$proyekSlug}-minggu-{$laporan->minggu_ke}-{$laporan->tahun}.pdf";
        }

        $safeFilename = Str::of($filename)
            ->replace(['/', '\\'], '-')
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9._-]+/', '-')
            ->trim('-')
            ->toString();

        if (!str_ends_with(strtolower($safeFilename), '.pdf')) {
            $safeFilename .= '.pdf';
        }

        $pdfContent = $mpdf->Output($safeFilename, Destination::STRING_RETURN);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $safeFilename . '"',
        ]);
    }
}
