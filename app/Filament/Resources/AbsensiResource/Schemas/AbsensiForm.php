<?php

namespace App\Filament\Resources\AbsensiResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
                            ->schema([self::dataAbsensiSection()])
                            ->visible(fn (string $operation) => in_array($operation, ['create', 'edit'])),
                        Tab::make('Absen Masuk')
                            ->schema([self::absenMasukSection()])
                            ->visible(fn (string $operation) => $operation === 'create'),
                        Tab::make('Absen Keluar')
                            ->schema([self::absenKeluarSection()])
                            ->visible(fn (string $operation) => $operation === 'edit'),
                        Tab::make('Informasi')
                            ->schema([self::informasiAbsensiSection()])
                            ->visible(fn (string $operation) => in_array($operation, ['edit', 'view'])),
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
                        'alpha' => 'Alpha',
                        'dinas_luar' => 'Dinas Luar',
                        'lembur' => 'Lembur',
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
                \Filament\Schemas\Components\View::make('filament.absensi.camera-widget')
                    ->viewData([
                        'tipe' => 'masuk',
                        'title' => 'Verifikasi Wajah Masuk',
                        'subtitle' => 'Posisikan wajah di tengah frame, lalu ambil foto.',
                    ])
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('foto_masuk')->dehydrated(true),

                \Filament\Schemas\Components\View::make('filament.absensi.gps-widget')
                    ->viewData([
                        'tipe' => 'masuk',
                        'title' => 'Validasi Lokasi Masuk',
                        'subtitle' => 'Sistem memverifikasi koordinat sebelum absensi disimpan.',
                    ])
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('latitude_masuk')->dehydrated(true),
                Forms\Components\Hidden::make('longitude_masuk')->dehydrated(true),
                Forms\Components\Hidden::make('akurasi_gps_masuk')->dehydrated(true),
                Forms\Components\Hidden::make('mock_location_detected_masuk')->dehydrated(true),

                Forms\Components\Hidden::make('ip_address_masuk')
                    ->default(fn () => request()->ip())
                    ->dehydrated(true),
                Forms\Components\Hidden::make('user_agent_masuk')
                    ->default(fn () => request()->userAgent())
                    ->dehydrated(true),
                Forms\Components\Hidden::make('device_id_masuk')
                    ->default(fn () => md5((string) request()->userAgent() . request()->ip()))
                    ->dehydrated(true),
            ]);
    }

    protected static function absenKeluarSection(): Component
    {
        return Section::make('Absen Keluar')
            ->description('Ambil foto selfie dan aktifkan GPS untuk absen keluar')
            ->schema([
                \Filament\Schemas\Components\View::make('filament.absensi.camera-widget')
                    ->viewData([
                        'tipe' => 'keluar',
                        'title' => 'Verifikasi Wajah Keluar',
                        'subtitle' => 'Ambil foto terbaru saat selesai kerja.',
                    ])
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('foto_keluar')->dehydrated(true),

                \Filament\Schemas\Components\View::make('filament.absensi.gps-widget')
                    ->viewData([
                        'tipe' => 'keluar',
                        'title' => 'Validasi Lokasi Keluar',
                        'subtitle' => 'Pastikan lokasi terbaca dengan akurat sebelum checkout.',
                    ])
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('latitude_keluar')->dehydrated(true),
                Forms\Components\Hidden::make('longitude_keluar')->dehydrated(true),
                Forms\Components\Hidden::make('akurasi_gps_keluar')->dehydrated(true),
                Forms\Components\Hidden::make('mock_location_detected_keluar')->dehydrated(true),

                Forms\Components\Hidden::make('ip_address_keluar')
                    ->default(fn () => request()->ip())
                    ->dehydrated(true),
                Forms\Components\Hidden::make('user_agent_keluar')
                    ->default(fn () => request()->userAgent())
                    ->dehydrated(true),
                Forms\Components\Hidden::make('device_id_keluar')
                    ->default(fn () => md5((string) request()->userAgent() . request()->ip()))
                    ->dehydrated(true),
            ]);
    }

    protected static function informasiAbsensiSection(): Component
    {
        return Section::make('Informasi Absensi')
            ->schema([
                Forms\Components\TextInput::make('user.name')->label('Karyawan')->disabled(),
                Forms\Components\TextInput::make('jam_masuk')->label('Jam Masuk')->disabled(),
                Forms\Components\TextInput::make('jam_keluar')->label('Jam Keluar')->disabled(),
                Forms\Components\TextInput::make('total_jam_kerja')->label('Total Jam Kerja')->suffix('jam')->disabled(),
                Forms\Components\TextInput::make('keterlambatan_menit')->label('Keterlambatan')->suffix('menit')->disabled(),
                Forms\Components\Toggle::make('mock_location_detected_masuk')->label('Fake GPS Terdeteksi (Masuk)')->disabled()->inline(false),
                Forms\Components\Toggle::make('mock_location_detected_keluar')->label('Fake GPS Terdeteksi (Keluar)')->disabled()->inline(false),
            ])
            ->columns(2);
    }
}
