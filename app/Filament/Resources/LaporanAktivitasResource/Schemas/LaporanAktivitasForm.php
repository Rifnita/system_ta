<?php

namespace App\Filament\Resources\LaporanAktivitasResource\Schemas;

use App\Models\KategoriLaporanAktivitas;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;

class LaporanAktivitasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Main Information Section
                Section::make('Informasi Task')
                    ->description('Informasi dasar tentang task yang akan dikerjakan')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_aktivitas')
                            ->label('Tanggal Task')
                            ->required()
                            ->default(today())
                            ->native(false)
                            ->prefixIcon('heroicon-o-calendar')
                            ->suffixAction(
                                Action::make('setTanggalToday')
                                    ->label('Hari Ini')
                                    ->icon('heroicon-o-calendar-days')
                                    ->action(fn (Set $set) => $set('tanggal_aktivitas', today()->format('Y-m-d')))
                            )
                            ->columnSpan(1),

                        Forms\Components\Select::make('kategori')
                            ->label('Kategori Task')
                            ->options(fn (): array => KategoriLaporanAktivitas::options())
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->prefixIcon('heroicon-o-tag')
                            ->columnSpan(1),

                        Forms\Components\Select::make('status')
                            ->label('Status Task')
                            ->options([
                                'pending' => 'Belum Dimulai',
                                'in_progress' => 'Sedang Dikerjakan',
                                'completed' => 'Selesai',
                                'failed' => 'Gagal',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false)
                            ->prefixIcon('heroicon-o-arrow-path')
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                // Auto set actual_start_time when status changed to in_progress
                                if ($state === 'in_progress') {
                                    $set('actual_start_time', now()->format('Y-m-d H:i'));
                                }
                                // Auto set actual_end_time when status changed to completed/failed
                                if (in_array($state, ['completed', 'failed'])) {
                                    $set('actual_end_time', now()->format('Y-m-d H:i'));
                                }
                            })
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_priority')
                            ->label('Task Prioritas')
                            ->helperText('Tandai jika task ini prioritas/urgent')
                            ->default(false)
                            ->inline(false)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('judul')
                            ->label('Judul Task')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Cek kondisi rumah di Perumahan Griya Asri')
                            ->prefixIcon('heroicon-o-pencil-square')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi Task')
                            ->required()
                            ->rows(4)
                            ->placeholder('Jelaskan detail task yang akan dikerjakan...')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('catatan_status')
                            ->label('Catatan Status')
                            ->rows(3)
                            ->placeholder('Tambahkan catatan terkait status task (wajib untuk status Gagal)')
                            ->requiredIf('status', 'failed')
                            ->visible(fn ($get) => in_array($get('status'), ['completed', 'failed', 'cancelled']))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->persistCollapsed(),

                // Time Planning Section
                Section::make('Planning Waktu')
                    ->description('Target dan waktu aktual pengerjaan task (wajib diisi)')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Forms\Components\DateTimePicker::make('target_start_time')
                            ->label('Target Mulai')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->prefixIcon('heroicon-o-play')
                            ->default(fn (string $context) => $context === 'create' ? now() : null)
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('target_end_time')
                            ->label('Target Selesai')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->prefixIcon('heroicon-o-stop')
                            ->after('target_start_time')
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('actual_start_time')
                            ->label('Waktu Mulai Aktual')
                            ->native(false)
                            ->seconds(false)
                            ->prefixIcon('heroicon-o-play-circle')
                            ->visible(fn ($get) => in_array($get('status'), ['in_progress', 'completed', 'failed']))
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('actual_end_time')
                            ->label('Waktu Selesai Aktual')
                            ->native(false)
                            ->seconds(false)
                            ->prefixIcon('heroicon-o-check-circle')
                            ->after('actual_start_time')
                            ->visible(fn ($get) => in_array($get('status'), ['completed', 'failed']))
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->persistCollapsed(),

                // Location Section
                Section::make('Lokasi Task')
                    ->description('Masukkan alamat, sistem akan generate link Google Maps otomatis')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\Textarea::make('alamat_lengkap')
                            ->label('Alamat Lengkap')
                            ->placeholder('Contoh: Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta')
                            ->rows(3)
                            ->live(debounce: 2000)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                if (empty($state) || strlen($state) < 10) {
                                    $set('lokasi', null);
                                    $set('latitude', null);
                                    $set('longitude', null);
                                    return;
                                }

                                try {
                                    // Geocoding menggunakan Nominatim (OpenStreetMap) - GRATIS
                                    /** @var \Illuminate\Http\Client\Response $response */
                                    $response = Http::timeout(10)
                                        ->withHeaders([
                                            'User-Agent' => 'Laravel Task Management App/1.0'
                                        ])
                                        ->get('https://nominatim.openstreetmap.org/search', [
                                            'q' => $state,
                                            'format' => 'json',
                                            'limit' => 1,
                                            'addressdetails' => 1,
                                        ]);

                                    $data = $response->json();
                                    
                                    if ($response->successful() && is_array($data) && count($data) > 0) {
                                        $location = $data[0];
                                        $lat = (float) $location['lat'];
                                        $lng = (float) $location['lon'];
                                        
                                        // Simpan koordinat
                                        $set('latitude', $lat);
                                        $set('longitude', $lng);
                                        
                                        // Generate Google Maps URL
                                        $googleMapsUrl = "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}";
                                        $set('lokasi', $googleMapsUrl);
                                    } else {
                                        // Jika geocoding gagal, generate simple URL dari alamat
                                        $encodedAddress = urlencode($state);
                                        $googleMapsUrl = "https://www.google.com/maps/search/?api=1&query={$encodedAddress}";
                                        $set('lokasi', $googleMapsUrl);
                                    }
                                } catch (\Exception $e) {
                                    // Fallback: generate simple URL dari alamat
                                    $encodedAddress = urlencode($state);
                                    $googleMapsUrl = "https://www.google.com/maps/search/?api=1&query={$encodedAddress}";
                                    $set('lokasi', $googleMapsUrl);
                                }
                            })
                            ->helperText('Ketik alamat lengkap, link Google Maps akan dibuat otomatis dalam 2 detik')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('lokasi')
                            ->label('Link Google Maps')
                            ->placeholder('Link akan dibuat otomatis...')
                            ->suffixAction(
                                Action::make('open_maps')
                                    ->label('Buka Maps')
                                    ->icon('heroicon-o-map-pin')
                                    ->color('success')
                                    ->url(fn ($get) => $get('lokasi'), shouldOpenInNewTab: true)
                                    ->visible(fn ($get) => !empty($get('lokasi')))
                            )
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Link otomatis dibuat dari alamat. Klik "Buka Maps" untuk verifikasi lokasi di Google Maps.')
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('latitude'),
                        Forms\Components\Hidden::make('longitude'),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->collapsed(),

                // Documentation Section
                Section::make('Dokumentasi')
                    ->description('Upload foto dan dokumen bukti (opsional, wajib untuk task yang selesai/gagal)')
                    ->icon('heroicon-o-camera')
                    ->schema([
                        Forms\Components\FileUpload::make('foto_bukti')
                            ->label('Foto Bukti Aktivitas')
                            ->image()
                            ->multiple()
                            ->maxFiles(5)
                            ->directory('laporan-aktivitas/foto')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->downloadable()
                            ->openable()
                            ->reorderable()
                            ->helperText('Opsional: Upload foto aktivitas (Maks 5 foto, 2MB/foto)')
                            ->maxSize(2048)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('dokumen_bukti')
                            ->label('Dokumen Bukti Penyelesaian')
                            ->multiple()
                            ->maxFiles(5)
                            ->directory('laporan-aktivitas/dokumen')
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->downloadable()
                            ->openable()
                            ->helperText('Wajib upload dokumen bukti untuk task yang selesai atau gagal (PDF, Word, atau Image)')
                            ->requiredIf('status', fn ($get) => in_array($get('status'), ['completed', 'failed']))
                            ->visible(fn ($get) => in_array($get('status'), ['completed', 'failed']))
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->collapsed(),

                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id())
                    ->dehydrated(fn ($context) => $context === 'create'),
            ]);
    }
}
