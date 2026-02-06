<?php

namespace App\Filament\Resources\TugasSayaResource\Tables;

use App\Models\KategoriLaporanAktivitas;
use App\Models\LaporanAktivitas;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TugasSayaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_aktivitas')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-calendar')
                    ->weight('medium')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('judul')
                    ->label('Task Title')
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
                    ->label('Category')
                    ->color(fn (string $state): string => KategoriLaporanAktivitas::colorFor($state))
                    ->searchable()
                    ->icon('heroicon-o-tag'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
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
                    ->label('Start')
                    ->time('H:i')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('waktu_selesai')
                    ->label('Finish')
                    ->time('H:i')
                    ->icon('heroicon-o-stop-circle')
                    ->color('danger')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('durasi')
                    ->label('Duration')
                    ->getStateUsing(fn (LaporanAktivitas $record) => $record->durasi)
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-clock'),

                Tables\Columns\TextColumn::make('lokasi')
                    ->label('Location')
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
                    ->label('Photo Proof')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_aktivitas', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Category')
                    ->options(fn (): array => KategoriLaporanAktivitas::options())
                    ->multiple(),

                Tables\Filters\Filter::make('tanggal_aktivitas')
                    ->form([
                        Forms\Components\DatePicker::make('dari')
                            ->label('From Date')
                            ->native(false),
                        Forms\Components\DatePicker::make('sampai')
                            ->label('To Date')
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
                            $indicators['dari'] = 'From: ' . \Carbon\Carbon::parse($data['dari'])->format('d M Y');
                        }
                        if ($data['sampai'] ?? null) {
                            $indicators['sampai'] = 'To: ' . \Carbon\Carbon::parse($data['sampai'])->format('d M Y');
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
                                ->label('Task Status')
                                ->options([
                                    'pending' => 'Not Started',
                                    'in_progress' => 'In Progress',
                                    'completed' => 'Completed',
                                    'failed' => 'Failed',
                                    'cancelled' => 'Cancelled',
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
                                ->label('Notes')
                                ->rows(3)
                                ->placeholder('Add notes about the status change...')
                                ->requiredIf('status', 'failed')
                                ->helperText('Required for Failed status')
                                ->visible(fn ($get) => in_array($get('status'), ['completed', 'failed', 'cancelled']))
                                ->columnSpanFull(),

                            Forms\Components\FileUpload::make('dokumen_bukti')
                                ->label('Proof Document')
                                ->multiple()
                                ->maxFiles(5)
                                ->directory('laporan-aktivitas/dokumen')
                                ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                ->downloadable()
                                ->helperText('Required for Completed or Failed tasks')
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
                        ->successNotificationTitle('Status updated successfully')
                        ->modalHeading('Update Task Status')
                        ->modalWidth('lg'),

                    EditAction::make()
                        ->color('warning'),
                    DeleteAction::make()
                        ->visible(fn (LaporanAktivitas $record): bool => Auth::user()?->can('delete', $record) ?? false)
                        ->color('danger'),
                ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('primary')
                ->button(),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Add Report')
                    ->icon('heroicon-o-plus'),
            ])
            ->emptyStateHeading('No tasks yet')
            ->emptyStateDescription('Start by adding your daily tasks.');
    }
}
