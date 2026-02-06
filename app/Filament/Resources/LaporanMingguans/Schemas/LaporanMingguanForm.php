<?php

namespace App\Filament\Resources\LaporanMingguans\Schemas;

use App\Models\Proyek;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LaporanMingguanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Periode & Proyek')
                    ->description('Pilih proyek dan periode pelaporan')
                    ->schema([
                        Select::make('proyek_id')
                            ->label('Proyek')
                            ->options(Proyek::aktif()->pluck('nama_proyek', 'id'))
                            ->searchable()
                            ->required()
                            ->preload()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Auto-suggest week number based on project
                                $proyek = Proyek::find($state);
                                if ($proyek && $proyek->tanggal_mulai) {
                                    $weeksSinceStart = Carbon::parse($proyek->tanggal_mulai)
                                        ->diffInWeeks(now()) + 1;
                                    $set('minggu_ke', $weeksSinceStart);
                                }
                            })
                            ->columnSpan(2),
                        
                        TextInput::make('minggu_ke')
                            ->label('Minggu Ke')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(52)
                            ->default(1)
                            ->helperText('Minggu ke berapa dalam tahun ini'),
                        
                        TextInput::make('tahun')
                            ->label('Tahun')
                            ->required()
                            ->numeric()
                            ->default(date('Y'))
                            ->minValue(2020)
                            ->maxValue(2100),
                        
                        DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai Periode')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now()->startOfWeek())
                            ->maxDate(fn ($get) => $get('tanggal_akhir')),
                        
                        DatePicker::make('tanggal_akhir')
                            ->label('Tanggal Akhir Periode')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now()->endOfWeek())
                            ->minDate(fn ($get) => $get('tanggal_mulai')),
                    ])
                    ->columns(3),

                Section::make('Progress & Pencapaian')
                    ->description('Catat progress pekerjaan minggu ini')
                    ->schema([
                        TextInput::make('persentase_penyelesaian')
                            ->label('Persentase Penyelesaian Total')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->helperText('Progress keseluruhan proyek saat ini'),
                        
                        TextInput::make('target_mingguan')
                            ->label('Target Minggu Ini')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Target progress yang ingin dicapai minggu ini'),
                        
                        TextInput::make('realisasi_mingguan')
                            ->label('Realisasi Minggu Ini')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Progress yang berhasil dicapai minggu ini'),
                        
                        Textarea::make('area_dikerjakan')
                            ->label('Area/Zona yang Dikerjakan')
                            ->placeholder('Contoh: Pondasi Lt.1, Struktur Kolom, Atap Lantai 2, dll')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Textarea::make('pekerjaan_dilaksanakan')
                            ->label('Deskripsi Pekerjaan yang Dilaksanakan')
                            ->placeholder('Jelaskan detail pekerjaan yang dilakukan minggu ini...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Resource Management')
                    ->description('Sumber daya yang digunakan minggu ini')
                    ->schema([
                        Textarea::make('material_digunakan')
                            ->label('Material yang Digunakan')
                            ->placeholder('Contoh: Semen 50 sak, Besi 2 ton, Cat 20 kaleng, dll')
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        TextInput::make('jumlah_pekerja')
                            ->label('Jumlah Pekerja (Rata-rata/Hari)')
                            ->numeric()
                            ->suffix('orang')
                            ->minValue(0)
                            ->helperText('Rata-rata jumlah pekerja per hari'),
                        
                        Select::make('kondisi_cuaca')
                            ->label('Kondisi Cuaca Dominan')
                            ->options([
                                'cerah' => 'Cerah',
                                'berawan' => 'Berawan',
                                'hujan_ringan' => 'Hujan Ringan',
                                'hujan_lebat' => 'Hujan Lebat',
                            ])
                            ->native(false)
                            ->helperText('Kondisi cuaca yang mendominasi minggu ini'),
                    ])
                    ->columns(2),

                Section::make('Quality Control & Temuan')
                    ->description('Evaluasi kualitas dan temuan lapangan')
                    ->schema([
                        Select::make('status_kualitas')
                            ->label('Status Kualitas Pekerjaan')
                            ->options([
                                'excellent' => 'Sangat Baik',
                                'good' => 'Baik',
                                'fair' => 'Cukup',
                                'poor' => 'Buruk',
                            ])
                            ->native(false)
                            ->default('good'),
                        
                        Textarea::make('temuan')
                            ->label('Temuan (Baik/Buruk)')
                            ->placeholder('Catat temuan penting, baik positif maupun negatif...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Kendala & Solusi')
                    ->description('Masalah yang dihadapi dan penanganannya')
                    ->schema([
                        Textarea::make('kendala')
                            ->label('Kendala/Masalah yang Dihadapi')
                            ->placeholder('Jelaskan kendala atau masalah yang terjadi...')
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Textarea::make('solusi')
                            ->label('Solusi/Tindakan yang Diambil')
                            ->placeholder('Jelaskan solusi atau tindakan yang sudah/akan diambil...')
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Textarea::make('dampak_timeline')
                            ->label('Dampak terhadap Timeline')
                            ->placeholder('Apakah ada dampak terhadap jadwal? Estimasi keterlambatan?')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Dokumentasi Foto')
                    ->description('Upload foto progress (maksimal 5 foto)')
                    ->schema([
                        FileUpload::make('foto_progress')
                            ->label('Foto Progress')
                            ->image()
                            ->multiple()
                            ->maxFiles(5)
                            ->maxSize(5120)
                            ->directory('laporan-mingguan/fotos')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->helperText('Upload foto before/after, progress pekerjaan, atau kendala (max 5 foto @ 5MB)')
                            ->columnSpanFull(),
                    ]),

                Section::make('Planning & Catatan')
                    ->description('Rencana minggu depan dan catatan tambahan')
                    ->schema([
                        Textarea::make('rencana_minggu_depan')
                            ->label('Rencana Minggu Depan')
                            ->placeholder('Jelaskan rencana pekerjaan untuk minggu depan...')
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Textarea::make('catatan')
                            ->label('Catatan Tambahan')
                            ->placeholder('Informasi tambahan yang perlu dicatat...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Hidden::make('user_id')
                    ->default(Auth::id()),
                
                Hidden::make('submitted_at')
                    ->default(now()),
            ]);
    }
}
