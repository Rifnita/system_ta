<?php

namespace App\Filament\Resources\LaporanMingguans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use App\Models\Proyek;

class LaporanMingguansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('proyek.nama_proyek')
                    ->label('Proyek')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    })
                    ->weight('bold'),
                
                TextColumn::make('minggu_ke')
                    ->label('Minggu Ke')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('tanggal_mulai')
                    ->label('Periode')
                    ->date('d M Y')
                    ->description(fn ($record) => $record->tanggal_akhir?->format('d M Y'))
                    ->sortable(),
                
                TextColumn::make('persentase_penyelesaian')
                    ->label('Progress Total')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => match(true) {
                        $state >= 75 => 'success',
                        $state >= 50 => 'info',
                        $state >= 25 => 'warning',
                        default => 'danger',
                    })
                    ->weight('bold'),
                
                TextColumn::make('target_mingguan')
                    ->label('Target')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('realisasi_mingguan')
                    ->label('Realisasi')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($record) => $record->isTargetTercapai() ? 'success' : 'warning')
                    ->icon(fn ($record) => $record->isTargetTercapai() ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle')
                    ->toggleable(),
                
                TextColumn::make('status_kualitas')
                    ->label('Kualitas')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'excellent' => 'Sangat Baik',
                        'good' => 'Baik',
                        'fair' => 'Cukup',
                        'poor' => 'Buruk',
                        default => '-',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'excellent' => 'success',
                        'good' => 'info',
                        'fair' => 'warning',
                        'poor' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                
                ImageColumn::make('foto_progress')
                    ->label('Foto')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->toggleable(),
                
                TextColumn::make('user.name')
                    ->label('Pelapor')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('jumlah_pekerja')
                    ->label('Pekerja')
                    ->suffix(' org')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('kondisi_cuaca')
                    ->label('Cuaca')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cerah' => 'Cerah',
                        'berawan' => 'Berawan',
                        'hujan_ringan' => 'Hujan Ringan',
                        'hujan_lebat' => 'Hujan Lebat',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'cerah' => 'success',
                        'berawan' => 'gray',
                        'hujan_ringan' => 'warning',
                        'hujan_lebat' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (?string $state): string => match ($state) {
                        'cerah' => 'heroicon-o-sun',
                        'berawan' => 'heroicon-o-cloud',
                        'hujan_ringan' => 'heroicon-o-cloud',
                        'hujan_lebat' => 'heroicon-o-cloud',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('submitted_at')
                    ->label('Dilaporkan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('proyek_id')
                    ->label('Proyek')
                    ->options(Proyek::pluck('nama_proyek', 'id'))
                    ->searchable()
                    ->preload()
                    ->native(false),
                
                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(function () {
                        $years = [];
                        for ($i = date('Y'); $i >= 2020; $i--) {
                            $years[$i] = $i;
                        }
                        return $years;
                    })
                    ->native(false),
                
                SelectFilter::make('status_kualitas')
                    ->label('Status Kualitas')
                    ->options([
                        'excellent' => 'Sangat Baik',
                        'good' => 'Baik',
                        'fair' => 'Cukup',
                        'poor' => 'Buruk',
                    ])
                    ->native(false),
                
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn ($record) => route('laporan-mingguan.pdf', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal_mulai', 'desc')
            ->striped();
    }
}
