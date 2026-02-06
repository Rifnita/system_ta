<?php

namespace App\Http\Controllers;

use App\Models\LaporanMingguan;
use App\Services\LaporanMingguanPdfExporter;
use Illuminate\Http\Request;

class LaporanMingguanExportController extends Controller
{
    public function __construct(
        private LaporanMingguanPdfExporter $pdfExporter
    ) {}

    /**
     * Export single laporan mingguan to PDF
     */
    public function exportPdf(LaporanMingguan $laporanMingguan)
    {
        // Authorization check (optional - add policy if needed)
        // $this->authorize('view', $laporanMingguan);

        return $this->pdfExporter->download($laporanMingguan);
    }
}
