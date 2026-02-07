<?php

namespace App\Filament\Widgets;

use App\Models\LaporanAktivitas;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UpcomingDeadlineWidget extends BaseWidget
{
    protected static ?int $sort = 20;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Personal deadlines - only for staff/users
        $user = Auth::user();
        return $user && !$user->hasAnyRole(['super_admin', 'panel_user']);
    }

    public function getHeading(): ?string
    {
        return 'Upcoming Deadlines';
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
                    ->label('Task')
                    ->searchable()
                    ->weight('bold')
                    ->wrap()
                    ->limit(50),
                
                TextColumn::make('kategori')
                    ->label('Category')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'meeting' => 'info',
                        'development' => 'primary',
                        'review' => 'warning',
                        'documentation' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state ?? 'Other'))),
                
                TextColumn::make('target_end_time')
                    ->label('Deadline')
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
                    ->label('Time Left')
                    ->state(function (LaporanAktivitas $record): string {
                        if (!$record->target_end_time) {
                            return '-';
                        }
                        
                        $deadline = Carbon::parse($record->target_end_time);
                        $now = Carbon::now();
                        
                        if ($deadline->isToday()) {
                            $hours = $now->diffInHours($deadline, false);
                            if ($hours < 0) {
                                return 'Overdue';
                            }
                            return $hours . ' hours';
                        }
                        
                        if ($deadline->isTomorrow()) {
                            return 'Tomorrow';
                        }
                        
                        $days = $now->diffInDays($deadline, false);
                        if ($days < 0) {
                            return 'Overdue';
                        }
                        
                        return $days . ' days';
                    })
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        str_contains($state, 'Overdue') => 'danger',
                        str_contains($state, 'hours') || str_contains($state, 'Tomorrow') => 'warning',
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
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state ?? 'Pending'))),
                
                TextColumn::make('is_priority')
                    ->label('Priority')
                    ->badge()
                    ->color(fn ($state) => $state ? 'danger' : 'gray')
                    ->formatStateUsing(fn ($state) => $state ? 'High' : 'Normal')
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
        
        return $count > 0 ? "You have $count tasks with upcoming deadlines" : "No upcoming deadlines";
    }
}
