<?php

namespace App\Filament\Widgets;

use App\Models\LaporanAktivitas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class RecentTasksWidget extends BaseWidget
{
    protected static ?int $sort = 21;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user && ! $user->hasAnyRole(['super_admin', 'panel_user']);
    }

    public function getHeading(): ?string
    {
        return 'Aktivitas Tugas Terbaru';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LaporanAktivitas::query()
                    ->where('user_id', Auth::id())
                    ->with('user')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('judul')
                    ->label('Judul Tugas')
                    ->searchable()
                    ->weight('bold')
                    ->wrap()
                    ->limit(40),

                TextColumn::make('tanggal_aktivitas')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'meeting' => 'info',
                        'development' => 'primary',
                        'review' => 'warning',
                        'documentation' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'meeting' => 'Rapat',
                        'development' => 'Pengembangan',
                        'review' => 'Peninjauan',
                        'documentation' => 'Dokumentasi',
                        default => 'Lainnya',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'warning',
                        'pending' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'completed' => 'Selesai',
                        'in_progress' => 'Sedang Dikerjakan',
                        'pending' => 'Menunggu',
                        'cancelled' => 'Dibatalkan',
                        default => 'Menunggu',
                    }),

                TextColumn::make('is_priority')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn ($state) => $state ? 'danger' : 'gray')
                    ->formatStateUsing(fn ($state) => $state ? 'Tinggi' : 'Normal'),

                TextColumn::make('durasi')
                    ->label('Durasi')
                    ->toggleable(),

                TextColumn::make('lokasi')
                    ->label('Lokasi')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_aktivitas', 'desc')
            ->striped()
            ->paginated(false);
    }
}
