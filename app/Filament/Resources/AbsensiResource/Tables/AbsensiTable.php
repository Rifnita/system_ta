<?php

namespace App\Filament\Resources\AbsensiResource\Tables;

use App\Filament\Resources\AbsensiResource;
use App\Models\Absensi;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AbsensiTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::getColumns())
            ->filters(self::getFilters())
            ->actions(self::getActions())
            ->bulkActions(self::getBulkActions())
            ->defaultSort('tanggal', 'desc');
    }

    protected static function getColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('user.name')
                ->label('Karyawan')
                ->searchable()
                ->sortable(),
            
            Tables\Columns\TextColumn::make('tanggal')
                ->label('Tanggal')
                ->date('d M Y')
                ->sortable(),
            
            Tables\Columns\TextColumn::make('jam_masuk')
                ->label('Jam Masuk')
                ->time('H:i'),
            
            Tables\Columns\TextColumn::make('jam_keluar')
                ->label('Jam Keluar')
                ->time('H:i')
                ->placeholder('-'),
            
            Tables\Columns\TextColumn::make('keterlambatan_menit')
                ->label('Terlambat')
                ->suffix(' mnt')
                ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                ->sortable(),
            
            Tables\Columns\TextColumn::make('total_jam_kerja')
                ->label('Total Jam')
                ->suffix(' jam')
                ->placeholder('-'),
            
            Tables\Columns\IconColumn::make('mock_location_detected_masuk')
                ->label('Fake GPS')
                ->boolean()
                ->trueIcon('heroicon-o-exclamation-triangle')
                ->falseIcon('heroicon-o-check-circle')
                ->trueColor('danger')
                ->falseColor('success')
                ->tooltip(fn ($record) => $record->mock_location_detected_masuk ? 'Fake GPS terdeteksi!' : 'Lokasi valid'),
            
            Tables\Columns\TextColumn::make('ip_address_masuk')
                ->label('IP Address')
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected static function getFilters(): array
    {
        return [
            Tables\Filters\Filter::make('tanggal')
                ->form([
                    Forms\Components\DatePicker::make('dari'),
                    Forms\Components\DatePicker::make('sampai'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['dari'],
                            fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                        )
                        ->when(
                            $data['sampai'],
                            fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                        );
                }),
            
            Tables\Filters\TernaryFilter::make('mock_location_detected_masuk')
                ->label('Fake GPS')
                ->placeholder('Semua')
                ->trueLabel('Terdeteksi Fake GPS')
                ->falseLabel('GPS Valid'),
        ];
    }

    protected static function getActions(): array
    {
        return [
            ViewAction::make(),
            EditAction::make()
                ->visible(fn () => AbsensiResource::canDo('update')),
            Action::make('absen_keluar')
                ->label('Absen Keluar')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->color('success')
                ->visible(fn (Absensi $record) => 
                    Auth::check()
                    && (AbsensiResource::canDo('create') || AbsensiResource::canDo('update'))
                    && $record->canCheckoutBy(Auth::user())
                )
                ->url(fn (Absensi $record) => \App\Filament\Resources\AbsensiResource::getUrl('edit', ['record' => $record])),
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
