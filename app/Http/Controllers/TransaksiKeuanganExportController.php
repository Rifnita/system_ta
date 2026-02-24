<?php

namespace App\Http\Controllers;

use App\Models\TransaksiKeuangan;
use App\Models\User;
use App\Services\TransaksiKeuanganExcelExporter;
use App\Services\TransaksiKeuanganPdfExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TransaksiKeuanganExportController extends Controller
{
    public function exportPdf(Request $request, TransaksiKeuanganPdfExporter $exporter)
    {
        $this->authorizeExport();

        [$records, $meta] = $this->collectRecords($request);

        $filename = 'transaksi-keuangan-' . Carbon::parse($meta['start_date'])->format('Y-m-d')
            . '-sd-' . Carbon::parse($meta['end_date'])->format('Y-m-d') . '.pdf';

        return $exporter->download($records, $meta, $filename);
    }

    public function exportExcel(Request $request, TransaksiKeuanganExcelExporter $exporter)
    {
        $this->authorizeExport();

        [$records, $meta] = $this->collectRecords($request);

        $filename = 'transaksi-keuangan-' . Carbon::parse($meta['start_date'])->format('Y-m-d')
            . '-sd-' . Carbon::parse($meta['end_date'])->format('Y-m-d') . '.xlsx';

        return $exporter->download($records, $meta, $filename);
    }

    private function collectRecords(Request $request): array
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'jenis' => ['nullable', 'in:pemasukan,pengeluaran'],
            'status' => ['nullable', 'in:draft,tercatat'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'proyek_id' => ['nullable', 'integer', 'exists:proyek,id'],
            'kategori_id' => ['nullable', 'integer', 'exists:kategori_transaksi_keuangan,id'],
        ]);

        $startDate = Carbon::parse($data['start_date'])->toDateString();
        $endDate = Carbon::parse($data['end_date'])->toDateString();

        $query = TransaksiKeuangan::query()
            ->with(['user', 'kategori', 'proyek'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->orderBy('created_at');

        if (! $this->canViewAny()) {
            $query->where('user_id', Auth::id());
        } elseif (! empty($data['user_id'])) {
            $query->where('user_id', (int) $data['user_id']);
        }

        if (! empty($data['jenis'])) {
            $query->where('jenis', $data['jenis']);
        }

        if (! empty($data['status'])) {
            $query->where('status', $data['status']);
        }

        if (! empty($data['proyek_id'])) {
            $query->where('proyek_id', (int) $data['proyek_id']);
        }

        if (! empty($data['kategori_id'])) {
            $query->where('kategori_transaksi_keuangan_id', (int) $data['kategori_id']);
        }

        $records = $query->get();

        $pegawai = 'Semua Pegawai';
        if (! $this->canViewAny()) {
            $pegawai = Auth::user()?->name ?? '-';
        } elseif (! empty($data['user_id'])) {
            $pegawai = User::find((int) $data['user_id'])?->name ?? '-';
        }

        $totalPemasukan = (float) $records->where('jenis', 'pemasukan')->sum('nominal');
        $totalPengeluaran = (float) $records->where('jenis', 'pengeluaran')->sum('nominal');

        $meta = [
            'judul' => 'Laporan Transaksi Keuangan',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'periode' => Carbon::parse($startDate)->translatedFormat('d M Y') . ' - ' . Carbon::parse($endDate)->translatedFormat('d M Y'),
            'pegawai' => $pegawai,
            'dicetak_oleh' => Auth::user()?->name,
            'dicetak_pada' => now(),
            'total_transaksi' => $records->count(),
            'total_pemasukan' => $totalPemasukan,
            'total_pengeluaran' => $totalPengeluaran,
            'saldo' => $totalPemasukan - $totalPengeluaran,
        ];

        return [$records, $meta];
    }

    private function canViewAny(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->can('view_any_transaksi_keuangan') || $user->can('ViewAny:TransaksiKeuangan');
    }

    private function authorizeExport(): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $canExport = $user->can('export_transaksi_keuangan')
            || $user->can('Export:TransaksiKeuangan')
            || $user->can('view_any_transaksi_keuangan')
            || $user->can('ViewAny:TransaksiKeuangan')
            || $user->can('create_transaksi_keuangan')
            || $user->can('Create:TransaksiKeuangan');

        abort_unless($canExport, 403);
    }
}
