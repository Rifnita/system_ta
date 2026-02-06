<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Mingguan - {{ $laporan->proyek->nama_proyek }}</title>
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
            font-size: 11pt;
            margin-bottom: 2px;
            font-weight: bold;
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

        /* Info Box */
        .info-box {
            border: 2px solid #333;
            padding: 10px;
            margin-bottom: 15px;
            background: #f9f9f9;
        }
        .info-box table {
            width: 100%;
        }
        .info-box td {
            padding: 4px 0;
            vertical-align: top;
        }
        .info-box .label {
            width: 35%;
            font-weight: bold;
        }
        .info-box .colon {
            width: 3%;
        }

        /* Section */
        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 5px 10px;
            background: #333;
            color: white;
        }
        .section-content {
            padding: 10px;
            border: 1px solid #ddd;
            background: white;
        }

        /* Progress Bar */
        .progress-container {
            margin: 10px 0;
        }
        .progress-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .progress-bar-bg {
            width: 100%;
            height: 25px;
            background: #e0e0e0;
            border: 1px solid #999;
            position: relative;
        }
        .progress-bar-fill {
            height: 100%;
            background: #4CAF50;
            text-align: center;
            line-height: 25px;
            color: white;
            font-weight: bold;
        }

        /* Stats Table */
        table.stats { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 10px 0;
        }
        table.stats td { 
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        table.stats .stat-label {
            font-weight: bold;
            background: #f0f0f0;
        }
        table.stats .stat-value {
            font-size: 14pt;
            font-weight: bold;
            color: #2196F3;
        }

        /* Photo Grid */
        .photo-grid {
            display: table;
            width: 100%;
            margin: 10px 0;
        }
        .photo-item {
            display: table-cell;
            width: 48%;
            padding: 5px;
            text-align: center;
            vertical-align: top;
        }
        .photo {
            max-width: 100%;
            max-height: 60mm;
            border: 1px solid #ddd;
        }
        .photo-caption {
            font-size: 8pt;
            color: #666;
            margin-top: 3px;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
        }
        .badge-success { background: #4CAF50; color: white; }
        .badge-info { background: #2196F3; color: white; }
        .badge-warning { background: #FF9800; color: white; }
        .badge-danger { background: #f44336; color: white; }

        /* Footer */
        .footer-signature {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signature-row {
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 10px;
        }
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
            padding-top: 5px;
        }

        /* Print Info */
        .print-info {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>
<body>

    {{-- Kop Surat --}}
    <div class="kop-surat">
        <div class="kop-nama">{{ $meta['app_name'] }}</div>
        <div class="kop-subtitle">SISTEM MANAJEMEN PROYEK KONSTRUKSI</div>
        <div class="kop-alamat">Laporan Mingguan Pengawasan Proyek</div>
    </div>

    {{-- Title --}}
    <div class="title">
        <h2>LAPORAN MINGGUAN PROYEK</h2>
        <p style="margin: 5px 0;">Minggu ke-{{ $laporan->minggu_ke }} Tahun {{ $laporan->tahun }}</p>
    </div>

    {{-- Info Proyek --}}
    <div class="info-box">
        <table>
            <tr>
                <td class="label">Nama Proyek</td>
                <td class="colon">:</td>
                <td><strong>{{ $laporan->proyek->nama_proyek }}</strong></td>
            </tr>
            <tr>
                <td class="label">Kode Proyek</td>
                <td class="colon">:</td>
                <td>{{ $laporan->proyek->kode_proyek }}</td>
            </tr>
            <tr>
                <td class="label">Lokasi</td>
                <td class="colon">:</td>
                <td>{{ $laporan->proyek->lokasi }}</td>
            </tr>
            <tr>
                <td class="label">Tipe Bangunan</td>
                <td class="colon">:</td>
                <td>{{ $laporan->proyek->tipe_bangunan_label }}</td>
            </tr>
            <tr>
                <td class="label">Periode Laporan</td>
                <td class="colon">:</td>
                <td>{{ $laporan->tanggal_mulai->format('d M Y') }} s/d {{ $laporan->tanggal_akhir->format('d M Y') }}</td>
            </tr>
            <tr>
                <td class="label">Pelapor</td>
                <td class="colon">:</td>
                <td>{{ $laporan->user->name }}</td>
            </tr>
        </table>
    </div>

    {{-- Progress Section --}}
    <div class="section">
        <div class="section-title">1. PROGRESS & PENCAPAIAN</div>
        <div class="section-content">
            {{-- Progress Bar --}}
            <div class="progress-container">
                <div class="progress-label">Progress Keseluruhan Proyek: {{ $laporan->persentase_penyelesaian }}%</div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width: {{ $laporan->persentase_penyelesaian }}%">
                        {{ $laporan->persentase_penyelesaian }}%
                    </div>
                </div>
            </div>

            {{-- Stats Table --}}
            <table class="stats">
                <tr>
                    <td class="stat-label">Target Minggu Ini</td>
                    <td class="stat-label">Realisasi Minggu Ini</td>
                    <td class="stat-label">Selisih</td>
                </tr>
                <tr>
                    <td class="stat-value">{{ $laporan->target_mingguan ?? '-' }}%</td>
                    <td class="stat-value">{{ $laporan->realisasi_mingguan ?? '-' }}%</td>
                    <td class="stat-value" style="color: {{ $laporan->isTargetTercapai() ? '#4CAF50' : '#f44336' }}">
                        {{ $laporan->selisih_target_realisasi > 0 ? '+' : '' }}{{ $laporan->selisih_target_realisasi ?? '-' }}%
                    </td>
                </tr>
            </table>

            @if($laporan->area_dikerjakan)
                <p><strong>Area/Zona yang Dikerjakan:</strong><br>{{ $laporan->area_dikerjakan }}</p>
            @endif

            @if($laporan->pekerjaan_dilaksanakan)
                <p><strong>Pekerjaan yang Dilaksanakan:</strong><br>{{ $laporan->pekerjaan_dilaksanakan }}</p>
            @endif
        </div>
    </div>

    {{-- Resource Management --}}
    <div class="section">
        <div class="section-title">2. RESOURCE MANAGEMENT</div>
        <div class="section-content">
            @if($laporan->material_digunakan)
                <p><strong>Material yang Digunakan:</strong><br>{{ $laporan->material_digunakan }}</p>
            @endif

            @if($laporan->jumlah_pekerja)
                <p><strong>Jumlah Pekerja:</strong> {{ $laporan->jumlah_pekerja }} orang (rata-rata per hari)</p>
            @endif

            @if($laporan->kondisi_cuaca)
                <p><strong>Kondisi Cuaca Dominan:</strong> {{ $laporan->kondisi_cuaca_label }}</p>
            @endif
        </div>
    </div>

    {{-- Quality Control --}}
    <div class="section">
        <div class="section-title">3. QUALITY CONTROL & TEMUAN</div>
        <div class="section-content">
            @if($laporan->status_kualitas)
                <p>
                    <strong>Status Kualitas Pekerjaan:</strong>
                    <span class="badge badge-{{ match($laporan->status_kualitas) {
                        'excellent' => 'success',
                        'good' => 'info',
                        'fair' => 'warning',
                        'poor' => 'danger',
                        default => 'info'
                    } }}">
                        {{ $laporan->status_kualitas_label }}
                    </span>
                </p>
            @endif

            @if($laporan->temuan)
                <p><strong>Temuan:</strong><br>{{ $laporan->temuan }}</p>
            @endif
        </div>
    </div>

    {{-- Kendala & Solusi --}}
    @if($laporan->kendala || $laporan->solusi || $laporan->dampak_timeline)
        <div class="section">
            <div class="section-title">4. KENDALA & SOLUSI</div>
            <div class="section-content">
                @if($laporan->kendala)
                    <p><strong>Kendala/Masalah yang Dihadapi:</strong><br>{{ $laporan->kendala }}</p>
                @endif

                @if($laporan->solusi)
                    <p><strong>Solusi/Tindakan yang Diambil:</strong><br>{{ $laporan->solusi }}</p>
                @endif

                @if($laporan->dampak_timeline)
                    <p><strong>Dampak terhadap Timeline:</strong><br>{{ $laporan->dampak_timeline }}</p>
                @endif
            </div>
        </div>
    @endif

    {{-- Dokumentasi Foto --}}
    @if($laporan->foto_progress && count($laporan->foto_progress) > 0)
        <div class="section">
            <div class="section-title">5. DOKUMENTASI FOTO</div>
            <div class="section-content">
                <div class="photo-grid">
                    @foreach($laporan->foto_progress as $index => $foto)
                        @if($index % 2 == 0 && $index > 0)
                            </div><div class="photo-grid">
                        @endif
                        <div class="photo-item">
                            @php
                                $fotoPath = storage_path('app/public/' . $foto);
                            @endphp
                            @if(file_exists($fotoPath))
                                <img src="{{ $fotoPath }}" class="photo" alt="Foto {{ $index + 1 }}">
                                <div class="photo-caption">Foto {{ $index + 1 }}</div>
                            @else
                                <p style="color: #999;">Foto tidak ditemukan</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Rencana Minggu Depan --}}
    @if($laporan->rencana_minggu_depan)
        <div class="section">
            <div class="section-title">6. RENCANA MINGGU DEPAN</div>
            <div class="section-content">
                <p>{{ $laporan->rencana_minggu_depan }}</p>
            </div>
        </div>
    @endif

    {{-- Catatan Tambahan --}}
    @if($laporan->catatan)
        <div class="section">
            <div class="section-title">7. CATATAN TAMBAHAN</div>
            <div class="section-content">
                <p>{{ $laporan->catatan }}</p>
            </div>
        </div>
    @endif

    {{-- Signature --}}
    <div class="footer-signature">
        <div class="signature-row">
            <div class="signature-box">
                <p>Dilaporkan oleh,</p>
                <div class="signature-line">
                    <strong>{{ $laporan->user->name }}</strong><br>
                    {{ $laporan->submitted_at?->format('d M Y H:i') }}
                </div>
            </div>
            <div class="signature-box">
                <p>Diketahui oleh,</p>
                <div class="signature-line">
                    <strong>Supervisor</strong><br>
                    {{ now()->format('d M Y') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Print Info --}}
    <div class="print-info">
        <p>Dokumen ini dicetak oleh {{ $meta['dicetak_oleh'] }} pada {{ $meta['dicetak_pada']->format('d M Y H:i') }}</p>
        <p>Dokumen ini dibuat secara elektronik dan sah tanpa tanda tangan basah.</p>
    </div>

</body>
</html>
