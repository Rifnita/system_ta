<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use App\Models\LaporanAktivitas;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class UpcomingDeadlineWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 20;
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return 'Tenggat Waktu Mendatang';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn () => LaporanAktivitas::query()
                    ->where('user_id', Auth::id())
                    ->whereNotNull('target_end_time')
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->where('target_end_time', '>=', Carbon::now())
                    ->orderBy('target_end_time', 'asc')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('judul')
                    ->label('Tugas')
                    ->searchable()
                    ->weight('bold')
                    ->wrap()
                    ->limit(50),

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

                TextColumn::make('target_end_time')
                    ->label('Batas Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        Carbon::parse($state)->isToday() => 'danger',
                        Carbon::parse($state)->isTomorrow() => 'warning',
                        Carbon::parse($state)->diffInDays(Carbon::now()) <= 7 => 'info',
                        default => 'success',
                    }),

                TextColumn::make('days_remaining')
                    ->label('Sisa Waktu')
                    ->state(function (LaporanAktivitas $record): string {
                        if (! $record->target_end_time) {
                            return '-';
                        }

                        $deadline = Carbon::parse($record->target_end_time);
                        $now = Carbon::now();

                        if ($deadline->isToday()) {
                            $hours = $now->diffInHours($deadline, false);

                            if ($hours < 0) {
                                return 'Terlambat';
                            }

                            return $hours . ' jam';
                        }

                        if ($deadline->isTomorrow()) {
                            return 'Besok';
                        }

                        $days = $now->diffInDays($deadline, false);

                        if ($days < 0) {
                            return 'Terlambat';
                        }

                        return $days . ' hari';
                    })
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        str_contains($state, 'Terlambat') => 'danger',
                        str_contains($state, 'jam') || str_contains($state, 'Besok') => 'warning',
                        intval($state) <= 7 => 'info',
                        default => 'success',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'in_progress' => 'warning',
                        'pending' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'in_progress' => 'Sedang Dikerjakan',
                        'pending' => 'Menunggu',
                        default => 'Menunggu',
                    }),

                TextColumn::make('is_priority')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn ($state) => $state ? 'danger' : 'gray')
                    ->formatStateUsing(fn ($state) => $state ? 'Tinggi' : 'Normal')
                    ->toggleable(),
            ])
            ->defaultSort('target_end_time', 'asc')
            ->striped()
            ->paginated(false);
    }

    public function getDescription(): ?string
    {
        $count = LaporanAktivitas::where('user_id', Auth::id())
            ->whereNotNull('target_end_time')
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('target_end_time', '>=', Carbon::now())
            ->count();

        return $count > 0
            ? "Anda memiliki $count tugas dengan batas waktu mendatang"
            : 'Tidak ada batas waktu mendatang';
    }
}
