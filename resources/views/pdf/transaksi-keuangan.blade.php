<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $meta['judul'] ?? 'Laporan Transaksi Keuangan' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 8px 0; }
        .meta { margin-bottom: 12px; }
        .meta-row { margin-bottom: 3px; }
        .summary { margin: 10px 0; border: 1px solid #d1d5db; border-radius: 6px; padding: 8px; }
        .summary-row { margin-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .text-right { text-align: right; }
        .badge-in { color: #065f46; font-weight: bold; }
        .badge-out { color: #991b1b; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $meta['judul'] ?? 'Laporan Transaksi Keuangan' }}</h1>

    <div class="meta">
        <div class="meta-row"><strong>Periode:</strong> {{ $meta['periode'] ?? '-' }}</div>
        <div class="meta-row"><strong>Pegawai:</strong> {{ $meta['pegawai'] ?? '-' }}</div>
        <div class="meta-row"><strong>Dicetak oleh:</strong> {{ $meta['dicetak_oleh'] ?? '-' }}</div>
        <div class="meta-row"><strong>Dicetak pada:</strong> {{ isset($meta['dicetak_pada']) ? \Illuminate\Support\Carbon::parse($meta['dicetak_pada'])->format('d/m/Y H:i') : '-' }}</div>
    </div>

    <div class="summary">
        <div class="summary-row"><strong>Total Transaksi:</strong> {{ number_format((float) ($meta['total_transaksi'] ?? 0), 0, ',', '.') }}</div>
        <div class="summary-row"><strong>Total Pemasukan:</strong> Rp {{ number_format((float) ($meta['total_pemasukan'] ?? 0), 2, ',', '.') }}</div>
        <div class="summary-row"><strong>Total Pengeluaran:</strong> Rp {{ number_format((float) ($meta['total_pengeluaran'] ?? 0), 2, ',', '.') }}</div>
        <div class="summary-row"><strong>Saldo:</strong> Rp {{ number_format((float) ($meta['saldo'] ?? 0), 2, ',', '.') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Kategori</th>
                <th class="text-right">Nominal</th>
                <th>Metode</th>
                <th>Proyek</th>
                <th>Status</th>
                <th>Pencatat</th>
                <th>Referensi</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ optional($record->tanggal)->format('d/m/Y') }}</td>
                    <td class="{{ $record->jenis === 'pemasukan' ? 'badge-in' : 'badge-out' }}">{{ $record->jenis === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran' }}</td>
                    <td>{{ $record->kategori?->nama ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format((float) $record->nominal, 2, ',', '.') }}</td>
                    <td>
                        {{ match($record->metode_pembayaran) {
                            'transfer_bank' => 'Transfer Bank',
                            'e_wallet' => 'E-Wallet',
                            'kartu_debit' => 'Kartu Debit',
                            'kartu_kredit' => 'Kartu Kredit',
                            default => ucfirst(str_replace('_', ' ', $record->metode_pembayaran)),
                        } }}
                    </td>
                    <td>{{ $record->proyek?->nama_proyek ?? '-' }}</td>
                    <td>{{ $record->status === 'draft' ? 'Draft' : 'Tercatat' }}</td>
                    <td>{{ $record->user?->name ?? '-' }}</td>
                    <td>{{ $record->nomor_referensi ?? '-' }}</td>
                    <td>{{ $record->deskripsi ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">Tidak ada data transaksi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
