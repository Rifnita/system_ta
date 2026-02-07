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
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('nama_proyek')
                    ->label('Project Name')
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
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'rumah_tinggal' => 'Residential House',
                        'ruko' => 'Shophouse',
                        'gedung' => 'Building',
                        'villa' => 'Villa',
                        'apartemen' => 'Apartment',
                        'lainnya' => 'Others',
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
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'perencanaan' => 'Planning',
                        'dalam_pengerjaan' => 'In Progress',
                        'tertunda' => 'On Hold',
                        'selesai' => 'Completed',
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
                    ->label('Start Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('estimasi_selesai')
                    ->label('Est. Completion')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('nilai_kontrak')
                    ->label('Contract Value')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('kontraktor')
                    ->label('Contractor')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('luas_bangunan')
                    ->label('Building Area')
                    ->numeric()
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('luas_tanah')
                    ->label('Land Area')
                    ->numeric()
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'perencanaan' => 'Planning',
                        'dalam_pengerjaan' => 'In Progress',
                        'tertunda' => 'On Hold',
                        'selesai' => 'Completed',
                    ])
                    ->native(false),
                SelectFilter::make('tipe_bangunan')
                    ->label('Building Type')
                    ->options([
                        'rumah_tinggal' => 'Residential House',
                        'ruko' => 'Shophouse',
                        'gedung' => 'Building',
                        'villa' => 'Villa',
                        'apartemen' => 'Apartment',
                        'lainnya' => 'Others',
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
