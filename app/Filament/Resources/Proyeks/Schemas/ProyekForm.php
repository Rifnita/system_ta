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
                Section::make('Basic Project Information')
                    ->description('Project identity and general information')
                    ->schema([
                        TextInput::make('kode_proyek')
                            ->label('Project Code')
                            ->placeholder('Example: PRJ-2026-001')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        TextInput::make('nama_proyek')
                            ->label('Project Name')
                            ->placeholder('Example: Mr. Ahmad Residential House Construction')
                            ->required()
                            ->maxLength(255),
                        Select::make('tipe_bangunan')
                            ->label('Building Type')
                            ->options([
                                'rumah_tinggal' => 'Residential House',
                                'ruko' => 'Shophouse',
                                'gedung' => 'Building',
                                'villa' => 'Villa',
                                'apartemen' => 'Apartment',
                                'lainnya' => 'Others',
                            ])
                            ->default('rumah_tinggal')
                            ->required()
                            ->native(false),
                        Select::make('status')
                            ->label('Project Status')
                            ->options([
                                'perencanaan' => 'Planning',
                                'dalam_pengerjaan' => 'In Progress',
                                'tertunda' => 'On Hold',
                                'selesai' => 'Completed',
                            ])
                            ->default('perencanaan')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),
                
                Section::make('Location and Related Parties')
                    ->schema([
                        TextInput::make('lokasi')
                            ->label('Location/City')
                            ->placeholder('Example: Surabaya')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('alamat_lengkap')
                            ->label('Full Address')
                            ->placeholder('Detailed project address...')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('nama_pemilik')
                            ->label('Owner Name')
                            ->placeholder('Owner/client name')
                            ->maxLength(255),
                        TextInput::make('kontraktor')
                            ->label('Contractor/Foreman')
                            ->placeholder('Contractor or foreman name')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                Section::make('Timeline and Budget')
                    ->schema([
                        DatePicker::make('tanggal_mulai')
                            ->label('Start Date')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('estimasi_selesai')
                            ->label('Estimated Completion')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->after('tanggal_mulai'),
                        TextInput::make('nilai_kontrak')
                            ->label('Contract Value')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('0')
                            ->helperText('Contract value in Rupiah'),
                    ])
                    ->columns(3),
                
                Section::make('Building Specifications')
                    ->schema([
                        TextInput::make('luas_tanah')
                            ->label('Land Area')
                            ->numeric()
                            ->suffix('m²')
                            ->placeholder('0'),
                        TextInput::make('luas_bangunan')
                            ->label('Building Area')
                            ->numeric()
                            ->suffix('m²')
                            ->placeholder('0'),
                        Textarea::make('deskripsi')
                            ->label('Description/Notes')
                            ->placeholder('Additional information about the project...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
