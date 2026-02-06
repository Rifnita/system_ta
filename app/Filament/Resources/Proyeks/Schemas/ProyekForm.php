<?php

namespace App\Filament\Resources\Proyeks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProyekForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar Proyek')
                    ->description('Data identitas dan informasi umum proyek')
                    ->schema([
                        TextInput::make('kode_proyek')
                            ->label('Kode Proyek')
                            ->placeholder('Contoh: PRJ-2026-001')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        TextInput::make('nama_proyek')
                            ->label('Nama Proyek')
                            ->placeholder('Contoh: Pembangunan Rumah Tinggal Bapak Ahmad')
                            ->required()
                            ->maxLength(255),
                        Select::make('tipe_bangunan')
                            ->label('Tipe Bangunan')
                            ->options([
                                'rumah_tinggal' => 'Rumah Tinggal',
                                'ruko' => 'Ruko',
                                'gedung' => 'Gedung',
                                'villa' => 'Villa',
                                'apartemen' => 'Apartemen',
                                'lainnya' => 'Lainnya',
                            ])
                            ->default('rumah_tinggal')
                            ->required()
                            ->native(false),
                        Select::make('status')
                            ->label('Status Proyek')
                            ->options([
                                'perencanaan' => 'Perencanaan',
                                'dalam_pengerjaan' => 'Dalam Pengerjaan',
                                'tertunda' => 'Tertunda',
                                'selesai' => 'Selesai',
                            ])
                            ->default('perencanaan')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),
                
                Section::make('Lokasi dan Pihak Terkait')
                    ->schema([
                        TextInput::make('lokasi')
                            ->label('Lokasi/Kota')
                            ->placeholder('Contoh: Surabaya')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('alamat_lengkap')
                            ->label('Alamat Lengkap')
                            ->placeholder('Alamat detail proyek...')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('nama_pemilik')
                            ->label('Nama Pemilik')
                            ->placeholder('Nama pemilik/klien')
                            ->maxLength(255),
                        TextInput::make('kontraktor')
                            ->label('Kontraktor/Mandor')
                            ->placeholder('Nama kontraktor atau mandor')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                Section::make('Timeline dan Anggaran')
                    ->schema([
                        DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('estimasi_selesai')
                            ->label('Estimasi Selesai')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->after('tanggal_mulai'),
                        TextInput::make('nilai_kontrak')
                            ->label('Nilai Kontrak')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('0')
                            ->helperText('Nilai kontrak dalam Rupiah'),
                    ])
                    ->columns(3),
                
                Section::make('Spesifikasi Bangunan')
                    ->schema([
                        TextInput::make('luas_tanah')
                            ->label('Luas Tanah')
                            ->numeric()
                            ->suffix('m²')
                            ->placeholder('0'),
                        TextInput::make('luas_bangunan')
                            ->label('Luas Bangunan')
                            ->numeric()
                            ->suffix('m²')
                            ->placeholder('0'),
                        Textarea::make('deskripsi')
                            ->label('Deskripsi/Catatan')
                            ->placeholder('Informasi tambahan tentang proyek...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
