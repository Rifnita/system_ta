<?php

namespace App\Filament\Resources\PengajuanCutis\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PengajuanCutiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Form Pengajuan Cuti')
                    ->description('Ajukan cuti dengan periode dan alasan yang jelas.')
                    ->schema([
                        Hidden::make('user_id')
                            ->default(fn () => Auth::id())
                            ->required(),

                        Select::make('jenis_cuti')
                            ->label('Jenis Cuti')
                            ->options([
                                'tahunan' => 'Cuti Tahunan',
                                'sakit' => 'Cuti Sakit',
                                'melahirkan' => 'Cuti Melahirkan',
                                'penting' => 'Cuti Alasan Penting',
                                'lainnya' => 'Lainnya',
                            ])
                            ->required()
                            ->native(false),

                        DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live(),

                        DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->minDate(fn ($get) => $get('tanggal_mulai'))
                            ->live(),

                        TextInput::make('jumlah_hari')
                            ->label('Jumlah Hari')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(true)
                            ->default(1)
                            ->helperText('Akan dihitung otomatis dari tanggal mulai dan tanggal selesai.'),

                        Textarea::make('alasan')
                            ->label('Alasan Pengajuan')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),

                        FileUpload::make('lampiran')
                            ->label('Lampiran (Opsional)')
                            ->directory('pengajuan-cuti/lampiran')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/png',
                                'image/jpeg',
                                'image/jpg',
                            ])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),

                        Textarea::make('catatan_approver')
                            ->label('Catatan Approver')
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn ($record) => filled($record?->catatan_approver)),
                    ])
                    ->columns(2),
            ]);
    }
}
