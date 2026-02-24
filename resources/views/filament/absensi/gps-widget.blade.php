@php
    $tipe = $tipe ?? 'masuk';
    $title = $title ?? 'Validasi Lokasi';
    $subtitle = $subtitle ?? 'Lokasi akan diverifikasi sebelum absensi disimpan.';

    $latPath = "data.latitude_{$tipe}";
    $lngPath = "data.longitude_{$tipe}";
    $accPath = "data.akurasi_gps_{$tipe}";
    $mockPath = "data.mock_location_detected_{$tipe}";
@endphp

<div
    x-data="attendanceGpsWidget({ latPath: '{{ $latPath }}', lngPath: '{{ $lngPath }}', accPath: '{{ $accPath }}', mockPath: '{{ $mockPath }}' })"
    x-init="init()"
    class="abs-widget"
>
    <div class="abs-widget__head">
        <div>
            <h3 class="abs-widget__title">{{ $title }}</h3>
            <p class="abs-widget__subtitle">{{ $subtitle }}</p>
        </div>
        <span class="abs-pill" :class="statusClass" x-text="statusText"></span>
    </div>

    <div class="abs-metrics">
        <div class="abs-metric">
            <span class="abs-metric__label">Latitude</span>
            <span class="abs-metric__value" x-text="latitude !== null ? latitude.toFixed(6) : '-'"></span>
        </div>
        <div class="abs-metric">
            <span class="abs-metric__label">Longitude</span>
            <span class="abs-metric__value" x-text="longitude !== null ? longitude.toFixed(6) : '-'"></span>
        </div>
        <div class="abs-metric">
            <span class="abs-metric__label">Akurasi</span>
            <span class="abs-metric__value" x-text="accuracy !== null ? Math.round(accuracy) + ' m' : '-'"></span>
        </div>
        <div class="abs-metric">
            <span class="abs-metric__label">Status Lokasi</span>
            <span class="abs-metric__value" :style="isMock ? 'color:#b91c1c' : 'color:#166534'" x-text="isMock ? 'Terindikasi spoofing' : 'Normal'"></span>
        </div>
    </div>

    <p x-show="error" x-cloak class="abs-alert abs-alert--danger" x-text="error"></p>
    <p x-show="isMock" x-cloak class="abs-alert abs-alert--warn">Sistem mendeteksi indikator lokasi tidak wajar. Matikan aplikasi fake GPS sebelum absen.</p>

    <div class="abs-actions">
        <x-filament::button type="button" color="gray" :icon="\Filament\Support\Icons\Heroicon::ArrowPath" x-on:click="getLocation" x-bind:disabled="loading">
            <span x-text="loading ? 'Memperbarui...' : 'Perbarui Lokasi'"></span>
        </x-filament::button>
        <span class="abs-hint">Izinkan akses lokasi browser untuk melanjutkan absensi.</span>
    </div>
</div>

@once
    <style>
        .abs-metrics {
            display: grid;
            gap: 8px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .abs-metric {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .abs-metric__label {
            font-size: 11px;
            color: #64748b;
        }
        .abs-metric__value {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
        }
        .abs-alert--warn {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }
        .abs-hint {
            font-size: 12px;
            color: #64748b;
            align-self: center;
        }
        @media (max-width: 640px) {
            .abs-metrics {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        window.attendanceGpsWidget = window.attendanceGpsWidget || function (config) {
            return {
                latPath: config.latPath,
                lngPath: config.lngPath,
                accPath: config.accPath,
                mockPath: config.mockPath,
                loading: false,
                latitude: null,
                longitude: null,
                accuracy: null,
                isMock: false,
                error: null,

                get statusText() {
                    if (this.error) return 'Gagal';
                    if (this.loading) return 'Mendeteksi lokasi';
                    if (this.latitude !== null && this.longitude !== null) return 'Lokasi tervalidasi';
                    return 'Menunggu lokasi';
                },

                get statusClass() {
                    if (this.error) return 'abs-pill--error';
                    if (this.loading) return 'abs-pill--warn';
                    if (this.latitude !== null && this.longitude !== null) return 'abs-pill--ok';
                    return 'abs-pill--saved';
                },

                init() {
                    this.getLocation();
                },

                getLocation() {
                    if (!navigator.geolocation) {
                        this.error = 'Browser tidak mendukung fitur lokasi.';
                        return;
                    }

                    this.loading = true;
                    this.error = null;

                    navigator.geolocation.getCurrentPosition(
                        (position) => this.handleSuccess(position),
                        (error) => this.handleError(error),
                        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                    );
                },

                handleSuccess(position) {
                    this.loading = false;
                    this.latitude = position.coords.latitude;
                    this.longitude = position.coords.longitude;
                    this.accuracy = position.coords.accuracy;
                    this.isMock = this.detectMockLocation(position);

                    this.$wire.set(this.latPath, this.latitude);
                    this.$wire.set(this.lngPath, this.longitude);
                    this.$wire.set(this.accPath, this.accuracy);
                    this.$wire.set(this.mockPath, this.isMock);
                },

                handleError(error) {
                    this.loading = false;

                    if (error.code === error.PERMISSION_DENIED) {
                        this.error = 'Izin lokasi ditolak. Aktifkan izin lokasi pada browser.';
                        return;
                    }
                    if (error.code === error.POSITION_UNAVAILABLE) {
                        this.error = 'Lokasi tidak tersedia. Pastikan GPS perangkat aktif.';
                        return;
                    }
                    if (error.code === error.TIMEOUT) {
                        this.error = 'Deteksi lokasi timeout. Silakan coba lagi.';
                        return;
                    }

                    this.error = 'Terjadi kesalahan saat mendeteksi lokasi.';
                },

                detectMockLocation(position) {
                    const suspiciousAccuracy = position.coords.accuracy !== null && position.coords.accuracy < 5;
                    const browserMockFlag = position.mocked === true;
                    return suspiciousAccuracy || browserMockFlag;
                },
            };
        };
    </script>
@endonce
