<?php

namespace App\Filament\Resources\TugasResource\Tables;

use App\Filament\Resources\TugasResource;
use App\Models\KategoriLaporanAktivitas;
use App\Models\LaporanAktivitas;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TugasTable
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
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Task')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    })
                    ->weight('bold')
                    ->wrap()
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
                        'pending' => 'Belum Dimulai',
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

                Tables\Columns\TextColumn::make('alamat_lengkap')
                    ->label('Lokasi')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    })
                    ->icon('heroicon-o-map-pin')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('target_start_time')
                    ->label('Target Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('actual_durasi')
                    ->label('Durasi Aktual')
                    ->getStateUsing(function (LaporanAktivitas $record) {
                        return $record->actual_durasi;
                    })
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-clock')
                    ->toggleable(),
            ])
            ->defaultSort('tanggal_aktivitas', 'desc')
            ->recordUrl(
                fn (LaporanAktivitas $record): string => TugasResource::getUrl('view', ['record' => $record])
            )
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Belum Dimulai',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                        'failed' => 'Gagal',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_priority')
                    ->label('Task Prioritas')
                    ->placeholder('Semua Task')
                    ->trueLabel('Hanya Prioritas')
                    ->falseLabel('Bukan Prioritas'),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pegawai')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'admin'])),

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
                ActionGroup::make([
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

                    Action::make('export_pdf')
                        ->label('Export PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (LaporanAktivitas $record) {
                            return redirect()->route('admin.laporan-aktivitas.export.single.pdf', $record);
                        }),
                    EditAction::make()
                        ->color('warning'),
                    DeleteAction::make()
                        ->color('danger'),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('primary')
                ->button(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada Tugas')
            ->emptyStateDescription('Belum ada tugas yang dibuat. Mulai buat tugas harian untuk pegawai!')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->poll('30s');
    }
}
