<?php

namespace App\Filament\Resources\TransaksiKeuangans\Pages;

use App\Filament\Resources\TransaksiKeuangans\TransaksiKeuanganResource;
use App\Models\KategoriTransaksiKeuangan;
use App\Models\Proyek;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListTransaksiKeuangans extends ListRecords
{
    protected static string $resource = TransaksiKeuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Transaksi'),

            Actions\Action::make('export_pdf')
                ->label('Ekspor PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->visible(fn (): bool => $this->canExport())
                ->modalHeading('Ekspor Transaksi Keuangan (PDF)')
                ->form($this->getExportFormSchema())
                ->action(function (array $data) {
                    if (! $this->canViewAny()) {
                        unset($data['user_id']);
                    }

                    return redirect()->route('admin.transaksi-keuangan.export.pdf', array_filter([
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
                        'jenis' => $data['jenis'] ?? null,
                        'status' => $data['status'] ?? null,
                        'user_id' => $data['user_id'] ?? null,
                        'proyek_id' => $data['proyek_id'] ?? null,
                        'kategori_id' => $data['kategori_id'] ?? null,
                    ], fn ($value) => $value !== null && $value !== ''));
                }),

            Actions\Action::make('export_excel')
                ->label('Ekspor Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->visible(fn (): bool => $this->canExport())
                ->modalHeading('Ekspor Transaksi Keuangan (Excel)')
                ->form($this->getExportFormSchema())
                ->action(function (array $data) {
                    if (! $this->canViewAny()) {
                        unset($data['user_id']);
                    }

                    return redirect()->route('admin.transaksi-keuangan.export.excel', array_filter([
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
                        'jenis' => $data['jenis'] ?? null,
                        'status' => $data['status'] ?? null,
                        'user_id' => $data['user_id'] ?? null,
                        'proyek_id' => $data['proyek_id'] ?? null,
                        'kategori_id' => $data['kategori_id'] ?? null,
                    ], fn ($value) => $value !== null && $value !== ''));
                }),
        ];
    }

    private function getExportFormSchema(): array
    {
        return [
            DatePicker::make('start_date')
                ->label('Tanggal Mulai')
                ->native(false)
                ->default(now()->startOfMonth())
                ->maxDate(fn ($get) => $get('end_date'))
                ->required(),

            DatePicker::make('end_date')
                ->label('Tanggal Selesai')
                ->native(false)
                ->default(now())
                ->minDate(fn ($get) => $get('start_date'))
                ->required(),

            Select::make('jenis')
                ->label('Jenis')
                ->options([
                    'pemasukan' => 'Pemasukan',
                    'pengeluaran' => 'Pengeluaran',
                ])
                ->native(false)
                ->placeholder('Semua jenis'),

            Select::make('status')
                ->label('Status')
                ->options([
                    'draft' => 'Draft',
                    'tercatat' => 'Tercatat',
                ])
                ->native(false)
                ->placeholder('Semua status'),

            Select::make('kategori_id')
                ->label('Kategori')
                ->options(fn (): array => KategoriTransaksiKeuangan::query()
                    ->orderBy('jenis')
                    ->orderBy('urutan')
                    ->orderBy('nama')
                    ->get()
                    ->mapWithKeys(fn (KategoriTransaksiKeuangan $kategori): array => [
                        $kategori->id => strtoupper($kategori->jenis) . ' - ' . $kategori->nama,
                    ])
                    ->toArray())
                ->searchable()
                ->preload()
                ->placeholder('Semua kategori'),

            Select::make('proyek_id')
                ->label('Proyek')
                ->options(fn (): array => Proyek::query()->orderBy('nama_proyek')->pluck('nama_proyek', 'id')->toArray())
                ->searchable()
                ->preload()
                ->placeholder('Semua proyek'),

            Select::make('user_id')
                ->label('Pegawai')
                ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable()
                ->preload()
                ->placeholder('Semua pegawai')
                ->visible(fn (): bool => $this->canViewAny()),
        ];
    }

    private function canViewAny(): bool
    {
        return TransaksiKeuanganResource::canViewAny();
    }

    private function canExport(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return TransaksiKeuanganResource::canViewAny()
            || TransaksiKeuanganResource::canCreate();
    }
}
