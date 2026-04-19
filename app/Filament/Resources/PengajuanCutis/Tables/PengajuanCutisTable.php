<?php

namespace App\Filament\Resources\PengajuanCutis\Tables;

use App\Models\PengajuanCuti;
use App\Notifications\PengajuanCutiStatusNotification;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PengajuanCutisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenis_cuti')
                    ->label('Jenis Cuti')
                    ->formatStateUsing(fn (string $state): string => self::labelJenisCuti($state))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Periode')
                    ->date('d M Y')
                    ->description(fn (PengajuanCuti $record): string => $record->tanggal_selesai?->format('d M Y') ?? '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah_hari')
                    ->label('Durasi')
                    ->suffix(' hari')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status_pengajuan')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::labelStatus($state))
                    ->color(fn (string $state): string => match ($state) {
                        'menunggu' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        'dibatalkan' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Diproses Oleh')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('disetujui_pada')
                    ->label('Waktu Proses')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_pengajuan')
                    ->label('Status')
                    ->options([
                        'menunggu' => 'Menunggu',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        'dibatalkan' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('jenis_cuti')
                    ->label('Jenis Cuti')
                    ->options([
                        'tahunan' => 'Cuti Tahunan',
                        'sakit' => 'Cuti Sakit',
                        'melahirkan' => 'Cuti Melahirkan',
                        'penting' => 'Cuti Alasan Penting',
                        'lainnya' => 'Lainnya',
                    ]),
            ])
            ->actions([
                Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PengajuanCuti $record): bool => self::canProcess() && $record->status_pengajuan === 'menunggu')
                    ->form([
                        Textarea::make('catatan_approver')
                            ->label('Catatan Approver')
                            ->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->action(function (PengajuanCuti $record, array $data): void {
                        $record->update([
                            'status_pengajuan' => 'disetujui',
                            'catatan_approver' => $data['catatan_approver'] ?? null,
                            'disetujui_oleh' => Auth::id(),
                            'disetujui_pada' => now(),
                        ]);

                        $record->refresh();
                        $record->user?->notify(new PengajuanCutiStatusNotification($record));
                    }),

                Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PengajuanCuti $record): bool => self::canProcess() && $record->status_pengajuan === 'menunggu')
                    ->form([
                        Textarea::make('catatan_approver')
                            ->label('Alasan Penolakan')
                            ->rows(3)
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (PengajuanCuti $record, array $data): void {
                        $record->update([
                            'status_pengajuan' => 'ditolak',
                            'catatan_approver' => $data['catatan_approver'],
                            'disetujui_oleh' => Auth::id(),
                            'disetujui_pada' => now(),
                        ]);

                        $record->refresh();
                        $record->user?->notify(new PengajuanCutiStatusNotification($record));
                    }),

                Action::make('batalkan')
                    ->label('Batalkan')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->visible(fn (PengajuanCuti $record): bool => self::canCancel($record))
                    ->requiresConfirmation()
                    ->action(function (PengajuanCuti $record): void {
                        $record->update([
                            'status_pengajuan' => 'dibatalkan',
                        ]);

                        $record->refresh();
                        $record->user?->notify(new PengajuanCutiStatusNotification($record));
                    }),

                EditAction::make()
                    ->visible(fn (PengajuanCuti $record): bool => self::canEdit($record)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    private static function canProcess(): bool
    {
        $user = Auth::user();

        return (bool) ($user && method_exists($user, 'hasRole')
            && ($user->hasRole('super_admin') || $user->hasRole('admin')));
    }

    private static function canEdit(PengajuanCuti $record): bool
    {
        if (self::canProcess()) {
            return true;
        }

        return (int) $record->user_id === (int) Auth::id()
            && $record->status_pengajuan === 'menunggu';
    }

    private static function canCancel(PengajuanCuti $record): bool
    {
        return (int) $record->user_id === (int) Auth::id()
            && $record->status_pengajuan === 'menunggu';
    }

    private static function labelStatus(string $status): string
    {
        return match ($status) {
            'menunggu' => 'Menunggu',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
            default => ucfirst($status),
        };
    }

    private static function labelJenisCuti(string $jenis): string
    {
        return match ($jenis) {
            'tahunan' => 'Cuti Tahunan',
            'sakit' => 'Cuti Sakit',
            'melahirkan' => 'Cuti Melahirkan',
            'penting' => 'Alasan Penting',
            'lainnya' => 'Lainnya',
            default => ucfirst($jenis),
        };
    }
}
