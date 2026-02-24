<?php

namespace App\Filament\Resources\PengaturanAbsensiResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PengaturanAbsensiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Form Pengaturan Absensi')
                    ->tabs([
                        Tab::make('Jam Kerja')
                            ->schema([
                                self::jamKerjaSection(),
                            ]),
                        Tab::make('Validasi')
                            ->schema([
                                self::pengaturanValidasiSection(),
                            ]),
                        Tab::make('Lokasi Kantor')
                            ->schema([
                                self::lokasiKantorSection(),
                            ]),
                        Tab::make('Status')
                            ->schema([
                                self::statusSection(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function jamKerjaSection(): Component
    {
        return Section::make('Jam Kerja')
            ->schema([
                Forms\Components\TimePicker::make('jam_masuk_standar')
                    ->label('Jam Masuk Standar')
                    ->seconds(false)
                    ->required(),
                
                Forms\Components\TimePicker::make('jam_keluar_standar')
                    ->label('Jam Keluar Standar')
                    ->seconds(false)
                    ->required(),
                
                Forms\Components\TextInput::make('toleransi_keterlambatan')
                    ->label('Toleransi Keterlambatan (menit)')
                    ->numeric()
                    ->suffix('menit')
                    ->required()
                    ->default(15)
                    ->helperText('Keterlambatan di bawah angka ini tidak akan dihitung'),
            ])
            ->columns(3);
    }

    protected static function pengaturanValidasiSection(): Component
    {
        return Section::make('Pengaturan Validasi')
            ->schema([
                Forms\Components\Toggle::make('wajib_foto')
                    ->label('Wajib Foto Selfie')
                    ->helperText('Karyawan harus mengambil foto saat absen')
                    ->default(true)
                    ->inline(false),
                
                Forms\Components\Toggle::make('wajib_lokasi')
                    ->label('Wajib Deteksi Lokasi GPS')
                    ->helperText('Karyawan harus berada dalam radius kantor')
                    ->default(true)
                    ->inline(false)
                    ->reactive(),
            ])
            ->columns(2);
    }

    protected static function lokasiKantorSection(): Component
    {
        return Section::make('Lokasi Kantor')
            ->schema([
                Forms\Components\TextInput::make('nama_lokasi')
                    ->label('Nama Lokasi')
                    ->default('Kantor Pusat')
                    ->required(),
                
                Forms\Components\TextInput::make('latitude_kantor')
                    ->label('Latitude')
                    ->numeric()
                    ->step(0.00000001)
                    ->placeholder('Contoh: -6.200000')
                    ->helperText('Koordinat latitude kantor (8 digit desimal)')
                    ->required(fn (Get $get) => $get('wajib_lokasi')),
                
                Forms\Components\TextInput::make('longitude_kantor')
                    ->label('Longitude')
                    ->numeric()
                    ->step(0.00000001)
                    ->placeholder('Contoh: 106.816666')
                    ->helperText('Koordinat longitude kantor (8 digit desimal)')
                    ->required(fn (Get $get) => $get('wajib_lokasi')),
                
                Forms\Components\TextInput::make('radius_kantor')
                    ->label('Radius Area Kantor')
                    ->numeric()
                    ->suffix('meter')
                    ->default(100)
                    ->required(fn (Get $get) => $get('wajib_lokasi'))
                    ->helperText('Jarak maksimal dari titik kantor yang diizinkan untuk absen'),

                Forms\Components\Placeholder::make('map_helper')
                    ->label('Cara Mendapatkan Koordinat')
                    ->content(new HtmlString('
                        1. Buka <a href="https://www.google.com/maps" target="_blank" class="text-primary-600 underline">Google Maps</a><br>
                        2. Klik kanan pada lokasi kantor<br>
                        3. Pilih koordinat yang muncul (akan otomatis copy)<br>
                        4. Paste di field Latitude dan Longitude
                    '))
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->visible(fn (Get $get) => $get('wajib_lokasi'));
    }

    protected static function statusSection(): Component
    {
        return Section::make('Status')
            ->schema([
                Forms\Components\Toggle::make('aktif')
                    ->label('Aktifkan Pengaturan Ini')
                    ->default(true)
                    ->inline(false)
                    ->helperText('Hanya satu pengaturan yang bisa aktif pada satu waktu'),
            ]);
    }
}
