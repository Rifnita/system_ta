<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $meta['judul'] ?? 'Laporan Aktivitas' }}</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 10pt; 
            color: #333; 
            line-height: 1.5;
        }

        /* Kop Surat */
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .kop-nama {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .kop-subtitle {
            font-size: 10pt;
            margin-bottom: 2px;
        }
        .kop-alamat {
            font-size: 9pt;
            color: #666;
        }

        /* Title */
        .title {
            text-align: center;
            margin: 20px 0;
        }
        .title h2 {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }

        /* Metadata */
        .metadata {
            margin-bottom: 15px;
        }
        .metadata table {
            width: 100%;
        }
        .metadata td {
            padding: 3px 0;
            vertical-align: top;
        }
        .metadata .label {
            width: 30%;
            font-weight: bold;
        }

        /* Summary */
        .summary {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 15px;
            background: #f9f9f9;
        }

        /* Table */
        table.data { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
        }
        table.data th { 
            background: #333;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            border: 1px solid #333;
            font-size: 10pt;
        }
        table.data td { 
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
            font-size: 10pt;
        }

        /* Photo */
        .photo {
            max-width: 100%;
            max-height: 35mm;
            border: 1px solid #ddd;
            margin: 2px 0;
        }

        /* Misc */
        .text-muted { color: #666; }
        .text-small { font-size: 8pt; }
        .text-center { text-align: center; }
        .nowrap { white-space: nowrap; }
        .bold { font-weight: bold; }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    {{-- Kop Surat --}}
    <div class="kop-surat">
        <div class="kop-nama">{{ $meta['app_name'] ?? config('app.name', 'SISTEM TA') }}</div>
        <div class="kop-subtitle">Sistem Informasi Laporan Aktivitas</div>
        <div class="kop-alamat">Jl. Contoh No. 123, Kota, Provinsi | Email: info@example.com | Telp: (021) 12345678</div>
    </div>

    {{-- Title --}}
    <div class="title">
        <h2>{{ strtoupper($meta['judul'] ?? 'LAPORAN AKTIVITAS') }}</h2>
    </div>

    {{-- Metadata --}}
    <div class="metadata">
        <table>
            <tr>
                <td class="label">Nama Pegawai</td>
                <td>: {{ $meta['pegawai'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Periode Laporan</td>
                <td>: {{ $meta['periode'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Dicetak Oleh</td>
                <td>: {{ $meta['dicetak_oleh'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Cetak</td>
                <td>: {{ \Illuminate\Support\Carbon::parse($meta['dicetak_pada'] ?? now())->format('d F Y, H:i') }} WIB</td>
            </tr>
        </table>
    </div>

    {{-- Summary --}}
    <div class="summary">
        <strong>Ringkasan:</strong>
        Total <strong>{{ (int) ($meta['total_aktivitas'] ?? 0) }}</strong> aktivitas dengan 
        durasi <strong>{{ intdiv((int) ($meta['total_durasi_menit'] ?? 0), 60) }} jam {{ ((int) ($meta['total_durasi_menit'] ?? 0)) % 60 }} menit</strong>
    </div>

    {{-- Data Table --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 9%;">Tanggal</th>
                <th style="width: 15%;">Judul</th>
                <th style="width: 20%;">Deskripsi</th>
                <th style="width: 9%;">Kategori</th>
                <th style="width: 10%;">Waktu</th>
                <th style="width: 6%;">Durasi</th>
                <th style="width: 18%;">Foto</th>
                <th style="width: 10%;">Lokasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $index => $record)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="nowrap">{{ optional($record->tanggal_aktivitas)->format('d M Y') ?? '-' }}</td>
                    <td class="bold">{{ $record->judul }}</td>
                    <td class="text-small">
                        @if (!empty($record->deskripsi))
                            {{ \Illuminate\Support\Str::limit($record->deskripsi, 100) }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $record->kategori }}</td>
                    <td class="text-small nowrap">
                        {{ $record->waktu_mulai ? \Illuminate\Support\Carbon::parse($record->waktu_mulai)->format('H:i') : '-' }}
                        -
                        {{ $record->waktu_selesai ? \Illuminate\Support\Carbon::parse($record->waktu_selesai)->format('H:i') : '-' }}
                    </td>
                    <td class="nowrap">{{ $record->durasi }}</td>
                    <td>
                        @php
                            $fotoBukti = collect((array) ($record->foto_bukti ?? []))
                                ->filter(fn ($v) => filled($v))
                                ->values();
                        @endphp

                        @if ($fotoBukti->isNotEmpty())
                            @foreach ($fotoBukti->take(2) as $foto)
                                @php
                                    $imageData = null;
                                    $mimeType = 'image/jpeg';
                                    
                                    if (is_string($foto)) {
                                        // Try multiple paths
                                        $paths = [
                                            storage_path('app/private/' . $foto),
                                            storage_path('app/public/' . $foto),
                                            storage_path('app/' . $foto),
                                        ];
                                        
                                        foreach ($paths as $absolutePath) {
                                            if (file_exists($absolutePath) && is_readable($absolutePath)) {
                                                $imageContent = @file_get_contents($absolutePath);
                                                if ($imageContent !== false && strlen($imageContent) > 0) {
                                                    $imageData = base64_encode($imageContent);
                                                    $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
                                                    $mimeType = match($extension) {
                                                        'jpg', 'jpeg' => 'image/jpeg',
                                                        'png' => 'image/png',
                                                        'gif' => 'image/gif',
                                                        default => 'image/jpeg',
                                                    };
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                @endphp

                                @if ($imageData)
                                    <img class="photo" src="data:{{ $mimeType }};base64,{{ $imageData }}" alt="Foto">
                                @endif
                            @endforeach
                            
                            @if ($fotoBukti->count() > 2)
                                <div class="text-small text-muted">+{{ $fotoBukti->count() - 2 }} foto</div>
                            @endif
                        @else
                            <span class="text-muted text-small">-</span>
                        @endif
                    </td>
                    <td class="text-small">
                        @if (!empty($record->lokasi))
                            {{ \Illuminate\Support\Str::limit($record->lokasi, 40) }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-muted text-center" style="padding: 20px;">
                        Tidak ada data aktivitas
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <div>{{ $meta['app_name'] ?? config('app.name') }} - Laporan Aktivitas</div>
        <div class="text-small">
            Dicetak pada {{ now()->format('d F Y H:i') }} WIB
        </div>
    </div>
</body>
</html>
