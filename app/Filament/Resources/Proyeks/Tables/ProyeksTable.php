<?php

namespace App\Filament\Resources\Proyeks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class ProyeksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_proyek')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('nama_proyek')
                    ->label('Nama Proyek')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),
                TextColumn::make('tipe_bangunan')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'rumah_tinggal' => 'Rumah Tinggal',
                        'ruko' => 'Ruko',
                        'gedung' => 'Gedung',
                        'villa' => 'Villa',
                        'apartemen' => 'Apartemen',
                        'lainnya' => 'Lainnya',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'rumah_tinggal' => 'info',
                        'ruko' => 'warning',
                        'gedung' => 'primary',
                        'villa' => 'success',
                        'apartemen' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('lokasi')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'perencanaan' => 'Perencanaan',
                        'dalam_pengerjaan' => 'Dalam Pengerjaan',
                        'tertunda' => 'Tertunda',
                        'selesai' => 'Selesai',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'perencanaan' => 'gray',
                        'dalam_pengerjaan' => 'primary',
                        'tertunda' => 'warning',
                        'selesai' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('tanggal_mulai')
                    ->label('Tgl Mulai')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('estimasi_selesai')
                    ->label('Estimasi Selesai')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('nilai_kontrak')
                    ->label('Nilai Kontrak')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('kontraktor')
                    ->label('Kontraktor')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('luas_bangunan')
                    ->label('Luas Bangunan')
                    ->numeric()
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('luas_tanah')
                    ->label('Luas Tanah')
                    ->numeric()
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'perencanaan' => 'Perencanaan',
                        'dalam_pengerjaan' => 'Dalam Pengerjaan',
                        'tertunda' => 'Tertunda',
                        'selesai' => 'Selesai',
                    ])
                    ->native(false),
                SelectFilter::make('tipe_bangunan')
                    ->label('Tipe Bangunan')
                    ->options([
                        'rumah_tinggal' => 'Rumah Tinggal',
                        'ruko' => 'Ruko',
                        'gedung' => 'Gedung',
                        'villa' => 'Villa',
                        'apartemen' => 'Apartemen',
                        'lainnya' => 'Lainnya',
                    ])
                    ->native(false),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
