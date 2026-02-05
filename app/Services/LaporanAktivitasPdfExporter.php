<?php

namespace App\Services;

use App\Models\LaporanAktivitas;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class LaporanAktivitasPdfExporter
{
    /**
     * @param  Collection<int, LaporanAktivitas>  $records
     * @param  array<string, mixed>  $meta
     */
    public function download(Collection $records, array $meta, string $filename)
    {
        $tempDir = storage_path('app/mpdf-temp');
        if (! File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $meta = array_merge([
            'app_name' => config('app.name'),
            'dicetak_oleh' => Auth::user()?->name,
            'dicetak_pada' => now(),
            'total_aktivitas' => $records->count(),
            'total_durasi_menit' => $this->sumDurasiMenit($records),
        ], $meta);

        $html = view('pdf.laporan-aktivitas', [
            'records' => $records,
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

        $mpdf->SetTitle($meta['judul'] ?? 'Laporan Aktivitas');
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

    /**
     * @param  Collection<int, LaporanAktivitas>  $records
     */
    private function sumDurasiMenit(Collection $records): int
    {
        return (int) $records->sum(function (LaporanAktivitas $record): int {
            if (! $record->waktu_mulai || ! $record->waktu_selesai) {
                return 0;
            }

            $mulai = $this->parseTimeToCarbon((string) $record->waktu_mulai);
            $selesai = $this->parseTimeToCarbon((string) $record->waktu_selesai);

            if (! $mulai || ! $selesai) {
                return 0;
            }

            return $mulai->diffInMinutes($selesai);
        });
    }

    private function parseTimeToCarbon(string $time): ?\Illuminate\Support\Carbon
    {
        try {
            if (\Illuminate\Support\Carbon::hasFormat($time, 'H:i:s')) {
                return \Illuminate\Support\Carbon::createFromFormat('H:i:s', $time);
            }

            if (\Illuminate\Support\Carbon::hasFormat($time, 'H:i')) {
                return \Illuminate\Support\Carbon::createFromFormat('H:i', $time);
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }
}
