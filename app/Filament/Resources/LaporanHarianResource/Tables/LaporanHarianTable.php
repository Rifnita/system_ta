<?php

namespace App\Filament\Resources\LaporanHarianResource\Tables;

use App\Models\KategoriLaporanAktivitas;
use App\Models\LaporanAktivitas;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LaporanHarianTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_aktivitas')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-calendar')
                    ->weight('medium')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Task')
                    ->searchable()
                    ->limit(50)
                    ->wrap()
                    ->weight('bold')
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->icon(fn (LaporanAktivitas $record) => $record->is_priority ? 'heroicon-o-star' : null)
                    ->iconColor(fn (LaporanAktivitas $record) => $record->is_priority ? 'warning' : null)
                    ->iconPosition('before'),

                Tables\Columns\BadgeColumn::make('kategori')
                    ->label('Kategori')
                    ->color(fn (string $state): string => KategoriLaporanAktivitas::colorFor($state))
                    ->searchable()
                    ->icon('heroicon-o-tag'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pending',
                        'in_progress' => 'Dikerjakan',
                        'completed' => 'Selesai',
                        'failed' => 'Gagal',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->color(fn ($record) => $record->status_color)
                    ->icon(fn ($state) => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'in_progress' => 'heroicon-o-arrow-path',
                        'completed' => 'heroicon-o-check-circle',
                        'failed' => 'heroicon-o-x-circle',
                        'cancelled' => 'heroicon-o-minus-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('waktu_mulai')
                    ->label('Mulai')
                    ->time('H:i')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('waktu_selesai')
                    ->label('Selesai')
                    ->time('H:i')
                    ->icon('heroicon-o-stop-circle')
                    ->color('danger')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('durasi')
                    ->label('Durasi')
                    ->getStateUsing(fn (LaporanAktivitas $record) => $record->durasi)
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-clock'),

                Tables\Columns\TextColumn::make('lokasi')
                    ->label('Lokasi')
                    ->searchable()
                    ->limit(30)
                    ->wrap()
                    ->toggleable()
                    ->icon('heroicon-o-map-pin')
                    ->color('info')
                    ->url(function (LaporanAktivitas $record): ?string {
                        $lokasi = (string) ($record->lokasi ?? '');

                        if ($lokasi === '') {
                            return null;
                        }

                        if (str_starts_with($lokasi, 'http://') || str_starts_with($lokasi, 'https://')) {
                            return $lokasi;
                        }

                        if (preg_match('/^-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?$/', $lokasi)) {
                            $coord = preg_replace('/\s+/', '', $lokasi);
                            return 'https://www.google.com/maps?q=' . $coord;
                        }

                        return null;
                    }, true)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\ImageColumn::make('foto_bukti')
                    ->label('Foto Bukti')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_aktivitas', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->options(fn (): array => KategoriLaporanAktivitas::options())
                    ->multiple(),

                Tables\Filters\Filter::make('tanggal_aktivitas')
                    ->form([
                        Forms\Components\DatePicker::make('dari')
                            ->label('Dari Tanggal')
                            ->native(false),
                        Forms\Components\DatePicker::make('sampai')
                            ->label('Sampai Tanggal')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_aktivitas', '>=', $date),
                            )
                            ->when(
                                $data['sampai'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_aktivitas', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari'] ?? null) {
                            $indicators['dari'] = 'Dari: ' . \Carbon\Carbon::parse($data['dari'])->format('d M Y');
                        }
                        if ($data['sampai'] ?? null) {
                            $indicators['sampai'] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Action::make('update_status')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status Task')
                            ->options([
                                'pending' => 'Belum Dimulai',
                                'in_progress' => 'Sedang Dikerjakan',
                                'completed' => 'Selesai',
                                'failed' => 'Gagal',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($set, $state) {
                                if ($state === 'in_progress') {
                                    $set('actual_start_time', now());
                                }
                                if (in_array($state, ['completed', 'failed'])) {
                                    $set('actual_end_time', now());
                                }
                            }),

                        Forms\Components\Textarea::make('catatan_status')
                            ->label('Catatan')
                            ->rows(3)
                            ->placeholder('Tambahkan catatan terkait perubahan status...')
                            ->requiredIf('status', 'failed')
                            ->helperText('Wajib diisi untuk status Gagal')
                            ->visible(fn ($get) => in_array($get('status'), ['completed', 'failed', 'cancelled']))
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('dokumen_bukti')
                            ->label('Dokumen Bukti Penyelesaian')
                            ->multiple()
                            ->maxFiles(5)
                            ->directory('laporan-aktivitas/dokumen')
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->downloadable()
                            ->helperText('Wajib upload dokumen bukti untuk task yang selesai atau gagal')
                            ->requiredIf('status', fn ($get) => in_array($get('status'), ['completed', 'failed']))
                            ->visible(fn ($get) => in_array($get('status'), ['completed', 'failed']))
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ])
                    ->fillForm(fn (LaporanAktivitas $record): array => [
                        'status' => $record->status,
                        'catatan_status' => $record->catatan_status,
                        'dokumen_bukti' => $record->dokumen_bukti,
                    ])
                    ->action(function (LaporanAktivitas $record, array $data): void {
                        // Auto set actual times based on status
                        if ($data['status'] === 'in_progress' && !$record->actual_start_time) {
                            $data['actual_start_time'] = now();
                        }
                        if (in_array($data['status'], ['completed', 'failed']) && !$record->actual_end_time) {
                            $data['actual_end_time'] = now();
                        }

                        $record->update($data);
                    })
                    ->successNotificationTitle('Status berhasil diupdate')
                    ->modalHeading('Update Status Task')
                    ->modalWidth('lg'),

                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (LaporanAktivitas $record): bool => Auth::user()?->can('delete', $record) ?? false),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tambah Laporan')
                    ->icon('heroicon-o-plus'),
            ])
            ->emptyStateHeading('Belum ada task harian')
            ->emptyStateDescription('Mulai dengan menambahkan laporan aktivitas harian Anda.');
    }
}
