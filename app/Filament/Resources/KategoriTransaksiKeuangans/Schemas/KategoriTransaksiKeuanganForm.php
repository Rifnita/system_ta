<?php

namespace App\Filament\Resources\KategoriTransaksiKeuangans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class KategoriTransaksiKeuanganForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Form Kategori Transaksi')
                    ->tabs([
                        Tab::make('Data Kategori')
                            ->schema([
                                Section::make('Informasi Kategori Transaksi')
                                    ->description('Kelola kategori pemasukan dan pengeluaran untuk transaksi keuangan.')
                                    ->schema([
                                        TextInput::make('nama')
                                            ->label('Nama Kategori')
                                            ->placeholder('Contoh: Pembelian Material')
                                            ->required()
                                            ->maxLength(100),
                                        Select::make('jenis')
                                            ->label('Jenis')
                                            ->options([
                                                'pemasukan' => 'Pemasukan',
                                                'pengeluaran' => 'Pengeluaran',
                                            ])
                                            ->required()
                                            ->native(false),
                                        TextInput::make('urutan')
                                            ->label('Urutan Tampil')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->helperText('Semakin kecil nilainya, semakin atas posisinya.')
                                            ->required(),
                                        Toggle::make('is_aktif')
                                            ->label('Aktif')
                                            ->default(true)
                                            ->inline(false),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
