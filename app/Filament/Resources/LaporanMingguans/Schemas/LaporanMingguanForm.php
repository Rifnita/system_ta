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
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LaporanMingguanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Form Laporan Mingguan')
                    ->tabs([
                        Tab::make('Periode')
                            ->schema([
                                Section::make('Periode & Informasi Proyek')
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
                            ->label('Minggu Ke-')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(52)
                            ->default(1)
                            ->helperText('Nomor minggu pada tahun ini'),
                        
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
                            ]),

                        Tab::make('Progres')
                            ->schema([
                                Section::make('Progres & Pencapaian')
                                    ->description('Catat progres pekerjaan minggu ini')
                                    ->schema([
                        TextInput::make('persentase_penyelesaian')
                            ->label('Persentase Penyelesaian Total')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->helperText('Progres keseluruhan proyek saat ini'),
                        
                        TextInput::make('target_mingguan')
                            ->label('Target Minggu Ini')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Target progres yang ingin dicapai minggu ini'),
                        
                        TextInput::make('realisasi_mingguan')
                            ->label('Realisasi Minggu Ini')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Progres yang tercapai minggu ini'),
                        
                        Textarea::make('area_dikerjakan')
                            ->label('Area/Zona yang Dikerjakan')
                            ->placeholder('Contoh: Pondasi Lantai 1, Struktur Kolom, Atap Lantai 2, dll')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Textarea::make('pekerjaan_dilaksanakan')
                            ->label('Deskripsi Pekerjaan yang Dilaksanakan')
                            ->placeholder('Jelaskan secara detail pekerjaan yang dilakukan minggu ini...')
                            ->rows(4)
                            ->columnSpanFull(),
                                    ])
                                    ->columns(3),
                            ]),

                        Tab::make('Sumber Daya')
                            ->schema([
                                Section::make('Manajemen Sumber Daya')
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
                            ->helperText('Kondisi cuaca yang dominan minggu ini'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Kualitas')
                            ->schema([
                                Section::make('Kontrol Kualitas & Temuan')
                                    ->description('Evaluasi kualitas dan temuan lapangan')
                                    ->schema([
                        Select::make('status_kualitas')
                            ->label('Status Kualitas Pekerjaan')
                            ->options([
                                'excellent' => 'Sangat Baik',
                                'good' => 'Baik',
                                'fair' => 'Cukup',
                                'poor' => 'Kurang',
                            ])
                            ->native(false)
                            ->default('good'),
                        
                        Textarea::make('temuan')
                            ->label('Temuan (Positif/Negatif)')
                            ->placeholder('Catat temuan penting, baik positif maupun negatif...')
                            ->rows(4)
                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),
                            ]),

                        Tab::make('Kendala')
                            ->schema([
                                Section::make('Kendala & Solusi')
                                    ->description('Masalah yang dihadapi dan cara penanganannya')
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
                            ->label('Dampak pada Timeline')
                            ->placeholder('Apakah ada dampak pada jadwal? Estimasi keterlambatan?')
                            ->rows(3)
                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),
                            ]),

                        Tab::make('Dokumentasi')
                            ->schema([
                                Section::make('Dokumentasi Foto')
                                    ->description('Unggah foto progres (maksimal 5 foto)')
                                    ->schema([
                        FileUpload::make('foto_progress')
                            ->label('Foto Progres')
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
                            ->helperText('Unggah foto sebelum/sesudah, progres kerja, atau kendala (maksimal 5 foto @ 5MB)')
                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Rencana')
                            ->schema([
                                Section::make('Rencana & Catatan')
                                    ->description('Rencana minggu depan dan catatan tambahan')
                                    ->schema([
                        Textarea::make('rencana_minggu_depan')
                            ->label('Rencana Minggu Depan')
                            ->placeholder('Jelaskan rencana kerja untuk minggu depan...')
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Textarea::make('catatan')
                            ->label('Catatan Tambahan')
                            ->placeholder('Informasi tambahan yang perlu dicatat...')
                            ->rows(3)
                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),
                            ]),
                    ])
                    ->columnSpanFull(),

                Hidden::make('user_id')
                    ->default(Auth::id()),
                
                Hidden::make('submitted_at')
                    ->default(now()),
            ]);
    }
}
