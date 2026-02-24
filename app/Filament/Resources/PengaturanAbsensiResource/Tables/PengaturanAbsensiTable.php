<?php

namespace App\Filament\Resources\PengaturanAbsensiResource\Tables;

use App\Models\PengaturanAbsensi;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class PengaturanAbsensiTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::getColumns())
            ->filters(self::getFilters())
            ->actions(self::getActions())
            ->bulkActions(self::getBulkActions());
    }

    protected static function getColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('nama_lokasi')
                ->label('Lokasi')
                ->searchable(),
            
            Tables\Columns\TextColumn::make('jam_masuk_standar')
                ->label('Jam Masuk')
                ->time('H:i'),
            
            Tables\Columns\TextColumn::make('jam_keluar_standar')
                ->label('Jam Keluar')
                ->time('H:i'),
            
            Tables\Columns\TextColumn::make('toleransi_keterlambatan')
                ->label('Toleransi')
                ->suffix(' mnt'),
            
            Tables\Columns\TextColumn::make('radius_kantor')
                ->label('Radius')
                ->suffix(' m'),
            
            Tables\Columns\IconColumn::make('wajib_foto')
                ->label('Foto')
                ->boolean(),
            
            Tables\Columns\IconColumn::make('wajib_lokasi')
                ->label('GPS')
                ->boolean(),
            
            Tables\Columns\ToggleColumn::make('aktif')
                ->label('Status')
                ->onColor('success')
                ->offColor('gray')
                ->beforeStateUpdated(function ($record, $state) {
                    if ($state) {
                        // Nonaktifkan pengaturan lain
                        PengaturanAbsensi::where('id', '!=', $record->id)
                            ->update(['aktif' => false]);
                    }
                }),
        ];
    }

    protected static function getFilters(): array
    {
        return [
            Tables\Filters\TernaryFilter::make('aktif')
                ->label('Status')
                ->placeholder('Semua')
                ->trueLabel('Aktif')
                ->falseLabel('Tidak Aktif'),
        ];
    }

    protected static function getActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    protected static function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }
}
