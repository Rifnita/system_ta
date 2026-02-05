<?php

namespace App\Filament\Resources\LaporanAktivitasResource\Tables;

use App\Models\LaporanAktivitas;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LaporanAktivitasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_aktivitas')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Aktivitas')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\BadgeColumn::make('kategori')
                    ->label('Kategori')
                    ->colors([
                        'primary' => 'Cek Rumah',
                        'success' => 'Survey Lokasi',
                        'warning' => 'Meeting Client',
                        'info' => 'Pemasangan',
                        'danger' => 'Perbaikan',
                        'secondary' => 'Administrasi',
                        'gray' => 'Lainnya',
                    ])
                    ->searchable(),

                Tables\Columns\TextColumn::make('waktu_mulai')
                    ->label('Waktu Mulai')
                    ->time('H:i')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('waktu_selesai')
                    ->label('Waktu Selesai')
                    ->time('H:i')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('durasi')
                    ->label('Durasi')
                    ->getStateUsing(function (LaporanAktivitas $record) {
                        return $record->durasi;
                    })
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('lokasi')
                    ->label('Lokasi')
                    ->searchable()
                    ->limit(30)
                    ->toggleable()
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
                    ->label('Foto')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_aktivitas', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pegawai')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => Auth::user()->can('view_any_laporan::aktivitas')),

                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->options([
                        'Cek Rumah' => 'Cek Rumah',
                        'Survey Lokasi' => 'Survey Lokasi',
                        'Meeting Client' => 'Meeting Client',
                        'Pemasangan' => 'Pemasangan',
                        'Perbaikan' => 'Perbaikan',
                        'Administrasi' => 'Administrasi',
                        'Lainnya' => 'Lainnya',
                    ])
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
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ])
            ->emptyStateHeading('Belum ada laporan aktivitas')
            ->emptyStateDescription('Mulai dengan menambahkan laporan aktivitas harian Anda.')
            ->poll('30s');
    }
}
