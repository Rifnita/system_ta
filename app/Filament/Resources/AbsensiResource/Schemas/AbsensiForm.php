<?php

namespace App\Filament\Resources\AbsensiResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AbsensiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Form Absensi')
                    ->tabs([
                        Tab::make('Data Absensi')
                            ->schema([
                                self::dataAbsensiSection(),
                            ]),
                        Tab::make('Absen Masuk')
                            ->schema([
                                self::absenMasukSection(),
                            ]),
                        Tab::make('Absen Keluar')
                            ->schema([
                                self::absenKeluarSection(),
                            ]),
                        Tab::make('Informasi')
                            ->schema([
                                self::informasiAbsensiSection(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function dataAbsensiSection(): Component
    {
        return Section::make('Data Absensi')
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id())
                    ->required(),
                
                Forms\Components\TextInput::make('tanggal')
                    ->label('Tanggal')
                    ->default(fn () => now()->format('Y-m-d'))
                    ->disabled()
                    ->dehydrated(true)
                    ->visible(fn (string $operation) => $operation === 'create'),
                
                Forms\Components\TextInput::make('jam_masuk')
                    ->label('Jam Masuk')
                    ->default(fn () => now()->format('H:i:s'))
                    ->disabled()
                    ->dehydrated(true)
                    ->visible(fn (string $operation) => $operation === 'create'),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'cuti' => 'Cuti',
                        'dinas_luar' => 'Dinas Luar',
                    ])
                    ->default('hadir')
                    ->required()
                    ->disabled(fn (string $operation) => $operation === 'create'),
                
                Forms\Components\Textarea::make('keterangan')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    protected static function absenMasukSection(): Component
    {
        return Section::make('Absen Masuk')
            ->description('Ambil foto selfie dan aktifkan GPS untuk absen masuk')
            ->schema([
                Forms\Components\Placeholder::make('camera_placeholder')
                    ->label('Foto Masuk')
                    ->content(fn () => new \Illuminate\Support\HtmlString('
                        <div x-data="cameraCapture()" x-init="init()">
                            <div class="space-y-4">
                                <div class="relative rounded-lg overflow-hidden bg-gray-900" style="max-width: 100%; aspect-ratio: 4/3;">
                                    <video x-ref="video" autoplay playsinline class="w-full h-full object-cover" x-show="!captured"></video>
                                    <img x-ref="preview" x-show="captured" class="w-full h-full object-cover" />
                                    
                                    <div class="absolute top-4 left-4 right-4">
                                        <div x-show="!cameraReady && !captured" class="bg-yellow-500 text-white px-4 py-2 rounded-lg text-sm">
                                            <span class="flex items-center gap-2">Mengakses kamera...</span>
                                        </div>
                                        <div x-show="cameraReady && !captured" class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm">
                                            <span class="flex items-center gap-2">Kamera siap</span>
                                        </div>
                                        <div x-show="error" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm" x-text="error"></div>
                                    </div>
                                </div>
                                
                                <div class="flex gap-2 justify-center">
                                    <button type="button" x-show="cameraReady && !captured" @click="capturePhoto" 
                                        class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold flex items-center gap-2">
                                        Ambil Foto
                                    </button>
                                    <button type="button" x-show="captured" @click="retakePhoto" 
                                        class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold flex items-center gap-2">
                                        Foto Ulang
                                    </button>
                                </div>
                                
                                <div x-show="captured" class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <p class="text-green-800 text-sm font-medium">✓ Foto berhasil diambil</p>
                                </div>
                            </div>
                        </div>
                        <script>
                            function cameraCapture() {
                                return {
                                    cameraReady: false, captured: false, photoData: null, error: null, stream: null,
                                    init() { this.startCamera(); },
                                    async startCamera() {
                                        try {
                                            this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user", width: { ideal: 1280 }, height: { ideal: 720 } } });
                                            this.$refs.video.srcObject = this.stream;
                                            this.cameraReady = true;
                                        } catch(err) { this.error = "Tidak dapat mengakses kamera"; }
                                    },
                                    capturePhoto() {
                                        const video = this.$refs.video, canvas = document.createElement("canvas");
                                        canvas.width = video.videoWidth; canvas.height = video.videoHeight;
                                        canvas.getContext("2d").drawImage(video, 0, 0);
                                        this.photoData = canvas.toDataURL("image/jpeg", 0.8);
                                        this.$refs.preview.src = this.photoData;
                                        this.captured = true; this.stopCamera();
                                        document.querySelector(\'[name="foto_masuk"]\').value = this.photoData;
                                    },
                                    retakePhoto() { this.captured = false; this.photoData = null; this.startCamera(); },
                                    stopCamera() { if(this.stream) { this.stream.getTracks().forEach(t => t.stop()); this.cameraReady = false; } }
                                }
                            }
                        </script>
                    '))
                    ->columnSpanFull()
                    ->visible(fn (string $operation) => $operation === 'create'),
                
                Forms\Components\Hidden::make('foto_masuk')
                    ->dehydrated(true),
                
                Forms\Components\Placeholder::make('gps_placeholder')
                    ->label('Lokasi GPS')
                    ->content(fn () => new \Illuminate\Support\HtmlString('
                        <div x-data="gpsDetector()" x-init="init()">
                            <div class="rounded-lg border-2 p-4" :class="gpsReady ? \'border-green-500 bg-green-50\' : \'border-yellow-500 bg-yellow-50\'">
                                <div x-show="loading">Mendeteksi lokasi...</div>
                                <div x-show="gpsReady && !error">
                                    <p class="text-sm font-semibold text-green-800">✓ Lokasi terdeteksi</p>
                                    <div class="mt-2 space-y-1 text-xs text-green-700">
                                        <p>Lat: <span x-text="latitude?.toFixed(6)"></span></p>
                                        <p>Long: <span x-text="longitude?.toFixed(6)"></span></p>
                                        <p>Akurasi: <span x-text="accuracy?.toFixed(0)"></span> meter</p>
                                    </div>
                                </div>
                                <div x-show="error" class="text-sm text-red-800" x-text="error"></div>
                                <div x-show="isMock" class="mt-3 p-3 bg-red-100 border border-red-300 rounded-lg">
                                    <p class="text-sm font-bold text-red-800">⚠️ FAKE GPS TERDETEKSI</p>
                                </div>
                            </div>
                        </div>
                        <script>
                            function gpsDetector() {
                                return {
                                    loading: false, gpsReady: false, latitude: null, longitude: null, accuracy: null, isMock: false, error: null,
                                    init() { this.getLocation(); },
                                    async getLocation() {
                                        this.loading = true;
                                        navigator.geolocation.getCurrentPosition(
                                            (pos) => {
                                                this.loading = false; this.gpsReady = true;
                                                this.latitude = pos.coords.latitude;
                                                this.longitude = pos.coords.longitude;
                                                this.accuracy = pos.coords.accuracy;
                                                this.isMock = pos.coords.accuracy < 5;
                                                document.querySelector(\'[name="latitude_masuk"]\').value = this.latitude;
                                                document.querySelector(\'[name="longitude_masuk"]\').value = this.longitude;
                                                document.querySelector(\'[name="akurasi_gps_masuk"]\').value = this.accuracy;
                                                document.querySelector(\'[name="mock_location_detected_masuk"]\').value = this.isMock ? 1 : 0;
                                            },
                                            (err) => { this.loading = false; this.error = "Gagal mendapatkan lokasi"; },
                                            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                                        );
                                    }
                                }
                            }
                        </script>
                    '))
                    ->columnSpanFull()
                    ->visible(fn (string $operation) => $operation === 'create'),
                
                Forms\Components\Hidden::make('latitude_masuk')
                    ->dehydrated(true),
                Forms\Components\Hidden::make('longitude_masuk')
                    ->dehydrated(true),
                Forms\Components\Hidden::make('akurasi_gps_masuk')
                    ->dehydrated(true),
                Forms\Components\Hidden::make('mock_location_detected_masuk')
                    ->dehydrated(true),
                
                Forms\Components\Hidden::make('ip_address_masuk')
                    ->default(fn () => request()->ip())
                    ->dehydrated(true),
                Forms\Components\Hidden::make('user_agent_masuk')
                    ->default(fn () => request()->userAgent())
                    ->dehydrated(true),
                Forms\Components\Hidden::make('device_id_masuk')
                    ->default(fn () => md5(request()->userAgent() . request()->ip()))
                    ->dehydrated(true),
            ])
            ->visible(fn (string $operation) => $operation === 'create');
    }

    protected static function absenKeluarSection(): Component
    {
        return Section::make('Absen Keluar')
            ->description('Ambil foto selfie dan aktifkan GPS untuk absen keluar')
            ->schema([
                Forms\Components\Placeholder::make('camera_keluar_placeholder')
                    ->label('Foto Keluar')
                    ->content(fn () => new \Illuminate\Support\HtmlString('
                        <div x-data="cameraCapture2()" x-init="init()">
                            <div class="space-y-4">
                                <div class="relative rounded-lg overflow-hidden bg-gray-900" style="max-width: 100%; aspect-ratio: 4/3;">
                                    <video x-ref="video" autoplay playsinline class="w-full h-full object-cover" x-show="!captured"></video>
                                    <img x-ref="preview" x-show="captured" class="w-full h-full object-cover" />
                                    
                                    <div class="absolute top-4 left-4 right-4">
                                        <div x-show="!cameraReady && !captured" class="bg-yellow-500 text-white px-4 py-2 rounded-lg text-sm">
                                            <span class="flex items-center gap-2">Mengakses kamera...</span>
                                        </div>
                                        <div x-show="cameraReady && !captured" class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm">
                                            <span class="flex items-center gap-2">Kamera siap</span>
                                        </div>
                                        <div x-show="error" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm" x-text="error"></div>
                                    </div>
                                </div>
                                
                                <div class="flex gap-2 justify-center">
                                    <button type="button" x-show="cameraReady && !captured" @click="capturePhoto" 
                                        class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold flex items-center gap-2">
                                        Ambil Foto
                                    </button>
                                    <button type="button" x-show="captured" @click="retakePhoto" 
                                        class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold flex items-center gap-2">
                                        Foto Ulang
                                    </button>
                                </div>
                                
                                <div x-show="captured" class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <p class="text-green-800 text-sm font-medium">✓ Foto berhasil diambil</p>
                                </div>
                            </div>
                        </div>
                        <script>
                            function cameraCapture2() {
                                return {
                                    cameraReady: false, captured: false, photoData: null, error: null, stream: null,
                                    init() { this.startCamera(); },
                                    async startCamera() {
                                        try {
                                            this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user", width: { ideal: 1280 }, height: { ideal: 720 } } });
                                            this.$refs.video.srcObject = this.stream;
                                            this.cameraReady = true;
                                        } catch(err) { this.error = "Tidak dapat mengakses kamera"; }
                                    },
                                    capturePhoto() {
                                        const video = this.$refs.video, canvas = document.createElement("canvas");
                                        canvas.width = video.videoWidth; canvas.height = video.videoHeight;
                                        canvas.getContext("2d").drawImage(video, 0, 0);
                                        this.photoData = canvas.toDataURL("image/jpeg", 0.8);
                                        this.$refs.preview.src = this.photoData;
                                        this.captured = true; this.stopCamera();
                                        document.querySelector(\'[name="foto_keluar"]\').value = this.photoData;
                                    },
                                    retakePhoto() { this.captured = false; this.photoData = null; this.startCamera(); },
                                    stopCamera() { if(this.stream) { this.stream.getTracks().forEach(t => t.stop()); this.cameraReady = false; } }
                                }
                            }
                        </script>
                    '))
                    ->columnSpanFull()
                    ->visible(fn (string $operation) => $operation === 'edit'),
                
                Forms\Components\Hidden::make('foto_keluar'),
                
                Forms\Components\Placeholder::make('gps_keluar_placeholder')
                    ->label('Lokasi GPS')
                    ->content(fn () => new \Illuminate\Support\HtmlString('
                        <div x-data="gpsDetector2()" x-init="init()">
                            <div class="rounded-lg border-2 p-4" :class="gpsReady ? \'border-green-500 bg-green-50\' : \'border-yellow-500 bg-yellow-50\'">
                                <div x-show="loading">Mendeteksi lokasi...</div>
                                <div x-show="gpsReady && !error">
                                    <p class="text-sm font-semibold text-green-800">✓ Lokasi terdeteksi</p>
                                    <div class="mt-2 space-y-1 text-xs text-green-700">
                                        <p>Lat: <span x-text="latitude?.toFixed(6)"></span></p>
                                        <p>Long: <span x-text="longitude?.toFixed(6)"></span></p>
                                        <p>Akurasi: <span x-text="accuracy?.toFixed(0)"></span> meter</p>
                                    </div>
                                </div>
                                <div x-show="error" class="text-sm text-red-800" x-text="error"></div>
                                <div x-show="isMock" class="mt-3 p-3 bg-red-100 border border-red-300 rounded-lg">
                                    <p class="text-sm font-bold text-red-800">⚠️ FAKE GPS TERDETEKSI</p>
                                </div>
                            </div>
                        </div>
                        <script>
                            function gpsDetector2() {
                                return {
                                    loading: false, gpsReady: false, latitude: null, longitude: null, accuracy: null, isMock: false, error: null,
                                    init() { this.getLocation(); },
                                    async getLocation() {
                                        this.loading = true;
                                        navigator.geolocation.getCurrentPosition(
                                            (pos) => {
                                                this.loading = false; this.gpsReady = true;
                                                this.latitude = pos.coords.latitude;
                                                this.longitude = pos.coords.longitude;
                                                this.accuracy = pos.coords.accuracy;
                                                this.isMock = pos.coords.accuracy < 5;
                                                document.querySelector(\'[name="latitude_keluar"]\').value = this.latitude;
                                                document.querySelector(\'[name="longitude_keluar"]\').value = this.longitude;
                                                document.querySelector(\'[name="akurasi_gps_keluar"]\').value = this.accuracy;
                                                document.querySelector(\'[name="mock_location_detected_keluar"]\').value = this.isMock ? 1 : 0;
                                            },
                                            (err) => { this.loading = false; this.error = "Gagal mendapatkan lokasi"; },
                                            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                                        );
                                    }
                                }
                            }
                        </script>
                    '))
                    ->columnSpanFull()
                    ->visible(fn (string $operation) => $operation === 'edit'),
                
                Forms\Components\Hidden::make('latitude_keluar'),
                Forms\Components\Hidden::make('longitude_keluar'),
                Forms\Components\Hidden::make('akurasi_gps_keluar'),
                Forms\Components\Hidden::make('mock_location_detected_keluar'),
                
                Forms\Components\Hidden::make('ip_address_keluar')
                    ->default(fn () => request()->ip()),
                Forms\Components\Hidden::make('user_agent_keluar')
                    ->default(fn () => request()->userAgent()),
                Forms\Components\Hidden::make('device_id_keluar')
                    ->default(fn () => md5(request()->userAgent() . request()->ip())),
            ])
            ->visible(fn (string $operation) => $operation === 'edit');
    }

    protected static function informasiAbsensiSection(): Component
    {
        return Section::make('Informasi Absensi')
            ->schema([
                Forms\Components\TextInput::make('user.name')
                    ->label('Karyawan')
                    ->disabled(),
                
                Forms\Components\TextInput::make('jam_masuk')
                    ->label('Jam Masuk')
                    ->disabled(),
                
                Forms\Components\TextInput::make('jam_keluar')
                    ->label('Jam Keluar')
                    ->disabled(),
                
                Forms\Components\TextInput::make('total_jam_kerja')
                    ->label('Total Jam Kerja')
                    ->suffix('jam')
                    ->disabled(),
                
                Forms\Components\TextInput::make('keterlambatan_menit')
                    ->label('Keterlambatan')
                    ->suffix('menit')
                    ->disabled(),
                
                Forms\Components\Toggle::make('mock_location_detected_masuk')
                    ->label('Fake GPS Terdeteksi (Masuk)')
                    ->disabled()
                    ->inline(false),
                
                Forms\Components\Toggle::make('mock_location_detected_keluar')
                    ->label('Fake GPS Terdeteksi (Keluar)')
                    ->disabled()
                    ->inline(false),
            ])
            ->columns(2)
            ->visible(fn (string $operation) => $operation === 'edit' || $operation === 'view');
    }
}
