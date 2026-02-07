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
                Section::make('Period & Project Information')
                    ->description('Select project and reporting period')
                    ->schema([
                        Select::make('proyek_id')
                            ->label('Project')
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
                            ->label('Week Number')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(52)
                            ->default(1)
                            ->helperText('Week number in this year'),
                        
                        TextInput::make('tahun')
                            ->label('Year')
                            ->required()
                            ->numeric()
                            ->default(date('Y'))
                            ->minValue(2020)
                            ->maxValue(2100),
                        
                        DatePicker::make('tanggal_mulai')
                            ->label('Period Start Date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now()->startOfWeek())
                            ->maxDate(fn ($get) => $get('tanggal_akhir')),
                        
                        DatePicker::make('tanggal_akhir')
                            ->label('Period End Date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now()->endOfWeek())
                            ->minDate(fn ($get) => $get('tanggal_mulai')),
                    ])
                    ->columns(3),

                Section::make('Progress & Achievement')
                    ->description('Record this week\'s work progress')
                    ->schema([
                        TextInput::make('persentase_penyelesaian')
                            ->label('Total Completion Percentage')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->helperText('Overall project progress at this time'),
                        
                        TextInput::make('target_mingguan')
                            ->label('This Week Target')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Target progress to be achieved this week'),
                        
                        TextInput::make('realisasi_mingguan')
                            ->label('This Week Realization')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Progress achieved this week'),
                        
                        Textarea::make('area_dikerjakan')
                            ->label('Area/Zone Worked On')
                            ->placeholder('Example: 1st Floor Foundation, Column Structure, 2nd Floor Roof, etc')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Textarea::make('pekerjaan_dilaksanakan')
                            ->label('Description of Work Performed')
                            ->placeholder('Explain in detail the work done this week...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Resource Management')
                    ->description('Resources used this week')
                    ->schema([
                        Textarea::make('material_digunakan')
                            ->label('Materials Used')
                            ->placeholder('Example: Cement 50 sacks, Steel 2 tons, Paint 20 cans, etc')
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        TextInput::make('jumlah_pekerja')
                            ->label('Number of Workers (Average/Day)')
                            ->numeric()
                            ->suffix('orang')
                            ->minValue(0)
                            ->helperText('Average number of workers per day'),
                        
                        Select::make('kondisi_cuaca')
                            ->label('Dominant Weather Condition')
                            ->options([
                                'cerah' => 'Sunny',
                                'berawan' => 'Cloudy',
                                'hujan_ringan' => 'Light Rain',
                                'hujan_lebat' => 'Heavy Rain',
                            ])
                            ->native(false)
                            ->helperText('Weather condition dominating this week'),
                    ])
                    ->columns(2),

                Section::make('Quality Control & Findings')
                    ->description('Quality evaluation and field findings')
                    ->schema([
                        Select::make('status_kualitas')
                            ->label('Work Quality Status')
                            ->options([
                                'excellent' => 'Excellent',
                                'good' => 'Good',
                                'fair' => 'Fair',
                                'poor' => 'Poor',
                            ])
                            ->native(false)
                            ->default('good'),
                        
                        Textarea::make('temuan')
                            ->label('Findings (Good/Bad)')
                            ->placeholder('Record important findings, both positive and negative...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Issues & Solutions')
                    ->description('Problems faced and how to handle them')
                    ->schema([
                        Textarea::make('kendala')
                            ->label('Issues/Problems Faced')
                            ->placeholder('Explain the issues or problems that occurred...')
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Textarea::make('solusi')
                            ->label('Solutions/Actions Taken')
                            ->placeholder('Explain the solutions or actions that have been/will be taken...')
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Textarea::make('dampak_timeline')
                            ->label('Impact on Timeline')
                            ->placeholder('Is there any impact on the schedule? Estimated delay?')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Photo Documentation')
                    ->description('Upload progress photos (maximum 5 photos)')
                    ->schema([
                        FileUpload::make('foto_progress')
                            ->label('Progress Photos')
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
                            ->helperText('Upload before/after photos, work progress, or issues (max 5 photos @ 5MB)')
                            ->columnSpanFull(),
                    ]),

                Section::make('Planning & Notes')
                    ->description('Next week\'s plan and additional notes')
                    ->schema([
                        Textarea::make('rencana_minggu_depan')
                            ->label('Next Week\'s Plan')
                            ->placeholder('Explain the work plan for next week...')
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Textarea::make('catatan')
                            ->label('Additional Notes')
                            ->placeholder('Additional information that needs to be recorded...')
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
