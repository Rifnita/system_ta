<?php

namespace App\Filament\Resources\LaporanAktivitasResource\Tables;

use App\Filament\Resources\LaporanAktivitasResource;
use App\Models\KategoriLaporanAktivitas;
use App\Models\LaporanAktivitas;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Action;
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
                    ->searchable()
                    ->icon('heroicon-o-calendar')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
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
                    })
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('kategori')
                    ->label('Kategori')
                    ->color(fn (string $state): string => KategoriLaporanAktivitas::colorFor($state))
                    ->searchable(),

                Tables\Columns\TextColumn::make('durasi')
                    ->label('Durasi')
                    ->getStateUsing(function (LaporanAktivitas $record) {
                        return $record->durasi;
                    })
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-clock'),
            ])
            ->defaultSort('tanggal_aktivitas', 'desc')
            ->recordUrl(
                fn (LaporanAktivitas $record): string => LaporanAktivitasResource::getUrl('view', ['record' => $record])
            )
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pegawai')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => Auth::user()->can('view_any_laporan::aktivitas')),

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
                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (LaporanAktivitas $record) {
                        return redirect()->route('admin.laporan-aktivitas.export.single.pdf', $record);
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada laporan aktivitas')
            ->emptyStateDescription('Belum ada data laporan aktivitas untuk ditampilkan.')
            ->poll('30s');
    }
}
