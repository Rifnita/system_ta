<?php

namespace App\Filament\Resources\LaporanAktivitasResource\Schemas;

use App\Models\KategoriLaporanAktivitas;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Auth;

class LaporanAktivitasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Aktivitas')
                    ->components([
                        Forms\Components\DatePicker::make('tanggal_aktivitas')
                            ->label('Tanggal Aktivitas')
                            ->required()
                            ->default(today())
                            ->maxDate(today())
                            ->native(false)
                            ->suffixAction(
                                Action::make('setTanggalToday')
                                    ->label('Today')
                                    ->icon('heroicon-o-calendar-days')
                                    ->action(fn (Set $set) => $set('tanggal_aktivitas', today()->format('Y-m-d')))
                            ),

                        Forms\Components\Select::make('kategori')
                            ->label('Kategori Aktivitas')
                            ->options(fn (): array => KategoriLaporanAktivitas::options())
                            ->required()
                            ->native(false)
                            ->searchable(),

                        Forms\Components\TextInput::make('judul')
                            ->label('Judul Aktivitas')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Cek kondisi rumah di Perumahan Griya Asri')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi Detail')
                            ->required()
                            ->rows(5)
                            ->placeholder('Jelaskan detail aktivitas yang dilakukan...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Waktu & Lokasi')
                    ->components([
                        Forms\Components\TimePicker::make('waktu_mulai')
                            ->label('Waktu Mulai')
                            ->required()
                            ->default(fn (string $context) => $context === 'create' ? now()->format('H:i') : null)
                            ->seconds(false)
                            ->native(false)
                            ->suffixAction(
                                Action::make('setWaktuMulaiNow')
                                    ->label('Now')
                                    ->icon('heroicon-o-clock')
                                    ->action(fn (Set $set) => $set('waktu_mulai', now()->format('H:i')))
                            ),

                        Forms\Components\TimePicker::make('waktu_selesai')
                            ->label('Waktu Selesai')
                            ->required()
                            ->default(fn (string $context) => $context === 'create' ? now()->format('H:i') : null)
                            ->seconds(false)
                            ->native(false)
                            ->after('waktu_mulai')
                            ->suffixAction(
                                Action::make('setWaktuSelesaiNow')
                                    ->label('Now')
                                    ->icon('heroicon-o-clock')
                                    ->action(fn (Set $set) => $set('waktu_selesai', now()->format('H:i')))
                            ),

                        Forms\Components\TextInput::make('lokasi')
                            ->label('Lokasi')
                            ->placeholder('Alamat/lokasi aktivitas (atau klik GPS untuk isi otomatis)')
                            ->maxLength(255)
                            ->helperText('GPS akan mengisi link Google Maps berdasarkan koordinat. Membutuhkan izin lokasi di browser dan biasanya bekerja di HTTPS / localhost.')
                            ->extraInputAttributes([
                                // Dipakai oleh tombol GPS untuk mengisi nilai.
                                'id' => 'laporan-aktivitas-lokasi',
                            ])
                            ->suffixAction(
                                Action::make('ambilLokasiGps')
                                    ->label('GPS')
                                    ->icon('heroicon-o-map-pin')
                                    // Jalankan di client (browser). Hindari request Livewire dengan stopImmediatePropagation.
                                    ->extraAttributes([
                                        'x-on:click.prevent' => <<<'JS'
$event.stopImmediatePropagation();

if (! navigator.geolocation) {
    alert('Browser tidak mendukung Geolocation.');
    return;
}

navigator.geolocation.getCurrentPosition(
    (pos) => {
        const value = `https://www.google.com/maps?q=${pos.coords.latitude},${pos.coords.longitude}`;
        const input = document.getElementById('laporan-aktivitas-lokasi');
        if (input) {
            input.value = value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    },
    (err) => {
        alert('Gagal mengambil lokasi: ' + (err?.message ?? err));
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
);
JS,
                                    ])
                                    // Fallback: kalau JS tidak jalan dan aksi tetap terpanggil ke server, jangan ngapa-ngapain.
                                    ->action(fn () => null)
                            )
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Dokumentasi')
                    ->components([
                        Forms\Components\FileUpload::make('foto_bukti')
                            ->label('Foto Bukti Aktivitas')
                            ->image()
                            ->multiple()
                            ->maxFiles(5)
                            ->directory('laporan-aktivitas')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->downloadable()
                            ->openable()
                            ->reorderable()
                            ->helperText('Upload maksimal 5 foto sebagai bukti aktivitas. Format: JPG, PNG (Maks 2MB per foto)')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id())
                    ->dehydrated(fn ($context) => $context === 'create'),
            ]);
    }
}
