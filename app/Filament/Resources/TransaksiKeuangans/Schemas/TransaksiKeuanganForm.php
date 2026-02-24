<?php

namespace App\Filament\Resources\TransaksiKeuangans\Schemas;

use App\Models\KategoriTransaksiKeuangan;
use App\Models\Proyek;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TransaksiKeuanganForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(fn () => Auth::id())
                    ->dehydrated(fn (string $operation): bool => $operation === 'create'),

                Tabs::make('Form Transaksi Keuangan')
                    ->tabs([
                        Tab::make('Data Transaksi')
                            ->schema([
                                Section::make('Informasi Transaksi')
                                    ->description('Isi data transaksi keuangan secara lengkap agar pencatatan rapi.')
                                    ->schema([
                                        DatePicker::make('tanggal')
                                            ->label('Tanggal Transaksi')
                                            ->default(now())
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->required(),
                                        Select::make('jenis')
                                            ->label('Jenis Transaksi')
                                            ->options([
                                                'pemasukan' => 'Pemasukan',
                                                'pengeluaran' => 'Pengeluaran',
                                            ])
                                            ->required()
                                            ->native(false)
                                            ->live()
                                            ->afterStateUpdated(fn (callable $set) => $set('kategori_transaksi_keuangan_id', null)),
                                        Select::make('kategori_transaksi_keuangan_id')
                                            ->label('Kategori')
                                            ->options(function (callable $get): array {
                                                return KategoriTransaksiKeuangan::query()
                                                    ->when($get('jenis'), fn ($query, $jenis) => $query->where('jenis', $jenis))
                                                    ->where('is_aktif', true)
                                                    ->orderBy('urutan')
                                                    ->orderBy('nama')
                                                    ->pluck('nama', 'id')
                                                    ->toArray();
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        TextInput::make('nominal')
                                            ->label('Nominal')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required()
                                            ->minValue(0),
                                        Select::make('metode_pembayaran')
                                            ->label('Metode Pembayaran')
                                            ->options([
                                                'kas' => 'Kas',
                                                'transfer_bank' => 'Transfer Bank',
                                                'e_wallet' => 'E-Wallet',
                                                'kartu_debit' => 'Kartu Debit',
                                                'kartu_kredit' => 'Kartu Kredit',
                                                'lainnya' => 'Lainnya',
                                            ])
                                            ->default('kas')
                                            ->required()
                                            ->native(false),
                                        Select::make('proyek_id')
                                            ->label('Terkait Proyek')
                                            ->options(fn (): array => Proyek::query()
                                                ->orderBy('nama_proyek')
                                                ->pluck('nama_proyek', 'id')
                                                ->toArray())
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Tidak terkait proyek tertentu'),
                                        Select::make('status')
                                            ->label('Status Pencatatan')
                                            ->options([
                                                'draft' => 'Draft',
                                                'tercatat' => 'Tercatat',
                                            ])
                                            ->default('tercatat')
                                            ->required()
                                            ->native(false),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Keterangan & Bukti')
                            ->schema([
                                Section::make('Detail Tambahan')
                                    ->schema([
                                        TextInput::make('nomor_referensi')
                                            ->label('Nomor Referensi')
                                            ->placeholder('Contoh: INV-2026-001')
                                            ->maxLength(100),
                                        Textarea::make('deskripsi')
                                            ->label('Deskripsi')
                                            ->rows(4)
                                            ->placeholder('Contoh: Pembelian semen dan pasir untuk proyek A')
                                            ->columnSpanFull(),
                                        FileUpload::make('lampiran_bukti')
                                            ->label('Lampiran Bukti')
                                            ->directory('transaksi-keuangan/bukti')
                                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                                            ->downloadable()
                                            ->openable()
                                            ->helperText('Upload bukti transaksi (opsional).')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
