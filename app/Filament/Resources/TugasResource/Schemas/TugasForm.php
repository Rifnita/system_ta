<?php

namespace App\Filament\Resources\TugasResource\Schemas;

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

class TugasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Main Information Section
                Section::make('Task Information')
                    ->description('Basic information about the task to be completed')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_aktivitas')
                            ->label('Task Date')
                            ->required()
                            ->default(today())
                            ->native(false)
                            ->prefixIcon('heroicon-o-calendar')
                            ->suffixAction(
                                Action::make('setTanggalToday')
                                    ->label('Today')
                                    ->icon('heroicon-o-calendar-days')
                                    ->action(fn (Set $set) => $set('tanggal_aktivitas', today()->format('Y-m-d')))
                            )
                            ->columnSpan(1),

                        Forms\Components\Select::make('kategori')
                            ->label('Task Category')
                            ->options(fn (): array => KategoriLaporanAktivitas::options())
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->prefixIcon('heroicon-o-tag')
                            ->columnSpan(1),

                        Forms\Components\Select::make('status')
                            ->label('Task Status')
                            ->options([
                                'pending' => 'Not Started',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'cancelled' => 'Cancelled',
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
                            ->label('Priority Task')
                            ->helperText('Mark if this task is priority/urgent')
                            ->default(false)
                            ->inline(false)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('judul')
                            ->label('Task Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Example: Check house condition in Griya Asri Housing')
                            ->prefixIcon('heroicon-o-pencil-square')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Task Description')
                            ->required()
                            ->rows(4)
                            ->placeholder('Explain details of the task to be completed...')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('catatan_status')
                            ->label('Status Notes')
                            ->rows(3)
                            ->placeholder('Add notes about task status (required for Failed status)')
                            ->requiredIf('status', 'failed')
                            ->visible(fn ($get) => in_array($get('status'), ['completed', 'failed', 'cancelled']))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->persistCollapsed(),

                // Time Planning Section
                Section::make('Time Planning')
                    ->description('Target and actual task completion time (required)')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Forms\Components\DateTimePicker::make('target_start_time')
                            ->label('Target Start')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->prefixIcon('heroicon-o-play')
                            ->default(fn (string $context) => $context === 'create' ? now() : null)
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('target_end_time')
                            ->label('Target Finish')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->prefixIcon('heroicon-o-stop')
                            ->after('target_start_time')
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('actual_start_time')
                            ->label('Actual Start Time')
                            ->native(false)
                            ->seconds(false)
                            ->prefixIcon('heroicon-o-play-circle')
                            ->visible(fn ($get) => in_array($get('status'), ['in_progress', 'completed', 'failed']))
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('actual_end_time')
                            ->label('Actual Finish Time')
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
                Section::make('Task Location')
                    ->description('Enter address, system will automatically generate Google Maps link')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\Textarea::make('alamat_lengkap')
                            ->label('Full Address')
                            ->placeholder('Example: Jl. Sudirman No. 123, Central Jakarta, DKI Jakarta')
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
                            ->helperText('Type full address, Google Maps link will be created automatically in 2 seconds')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('lokasi')
                            ->label('Google Maps Link')
                            ->placeholder('Link will be created automatically...')
                            ->suffixAction(
                                Action::make('open_maps')
                                    ->label('Open Maps')
                                    ->icon('heroicon-o-map-pin')
                                    ->color('success')
                                    ->url(fn ($get) => $get('lokasi'), shouldOpenInNewTab: true)
                                    ->visible(fn ($get) => !empty($get('lokasi')))
                            )
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Link automatically created from address. Click "Open Maps" to verify location on Google Maps.')
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('latitude'),
                        Forms\Components\Hidden::make('longitude'),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->collapsed(),

                // Documentation Section
                Section::make('Documentation')
                    ->description('Upload photos and proof documents (optional, required for completed/failed tasks)')
                    ->icon('heroicon-o-camera')
                    ->schema([
                        Forms\Components\FileUpload::make('foto_bukti')
                            ->label('Activity Proof Photos')
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
                            ->helperText('Optional: Upload activity photos (Max 5 photos, 2MB/photo)')
                            ->maxSize(2048)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('dokumen_bukti')
                            ->label('Completion Proof Document')
                            ->multiple()
                            ->maxFiles(5)
                            ->directory('laporan-aktivitas/dokumen')
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->downloadable()
                            ->openable()
                            ->helperText('Required for completed or failed tasks (PDF, Word, or Image)')
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
