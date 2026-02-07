<?php

namespace App\Filament\Widgets;

use App\Models\LaporanAktivitas;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeamLeaderboardWidget extends BaseWidget
{
    protected static ?int $sort = 9;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Only for managers and admins
        $user = Auth::user();
        return $user && $user->hasAnyRole(['super_admin', 'panel_user']);
    }

    public function getHeading(): ?string
    {
        return 'Top Performers This Month';
    }

    public function table(Table $table): Table
    {
        $thisMonth = Carbon::now()->startOfMonth();
        
        // Get users with their task completion stats
        $userStats = LaporanAktivitas::where('status', 'completed')
            ->where('updated_at', '>=', $thisMonth)
            ->select('user_id')
            ->selectRaw('COUNT(*) as completed_count')
            ->selectRaw('COUNT(CASE WHEN updated_at <= target_end_time THEN 1 END) as on_time_count')
            ->selectRaw('COUNT(CASE WHEN target_end_time IS NOT NULL THEN 1 END) as with_deadline_count')
            ->groupBy('user_id')
            ->orderByDesc('completed_count')
            ->limit(10)
            ->get()
            ->map(function ($stat) {
                $onTimeRate = $stat->with_deadline_count > 0 
                    ? round(($stat->on_time_count / $stat->with_deadline_count) * 100, 1)
                    : 0;
                
                $user = User::find($stat->user_id);
                
                return [
                    'user_id' => $stat->user_id,
                    'name' => $user?->name ?? 'Unknown',
                    'email' => $user?->email ?? '-',
                    'completed_count' => $stat->completed_count,
                    'on_time_count' => $stat->on_time_count,
                    'on_time_rate' => $onTimeRate,
                    'score' => ($stat->completed_count * 10) + ($onTimeRate * 0.5),
                ];
            })
            ->sortByDesc('score')
            ->values()
            ->take(10);

        // Get user IDs from stats
        $userIds = $userStats->pluck('user_id')->filter()->toArray();
        
        // If no users have stats, return empty query
        if (empty($userIds)) {
            return $table
                ->query(fn () => User::query()->whereRaw('1 = 0'))
                ->columns($this->getLeaderboardColumns($userStats))
                ->paginated(false);
        }

        return $table
            ->query(
                fn () => User::query()
                    ->whereIn('id', $userIds)
                    ->orderByRaw('FIELD(id, ' . implode(',', $userIds) . ')')
            )
            ->columns($this->getLeaderboardColumns($userStats))
            ->striped()
            ->paginated(false);
    }

    protected function getLeaderboardColumns($userStats): array
    {
        return [
            TextColumn::make('rank')
                ->label('#')
                ->state(function ($record) use ($userStats) {
                    $index = $userStats->search(function ($item) use ($record) {
                        return $item['user_id'] === $record->id;
                    });
                    return $index !== false ? $index + 1 : '-';
                })
                ->badge()
                ->color(fn ($state) => match (true) {
                    $state == 1 => 'success',
                    $state <= 3 => 'warning',
                    default => 'gray',
                })
                ->alignCenter(),
            
            TextColumn::make('name')
                ->label('Team Member')
                ->weight('bold')
                ->searchable(),
            
            TextColumn::make('email')
                ->label('Email')
                ->toggleable(isToggledHiddenByDefault: true),
            
            TextColumn::make('completed_tasks')
                ->label('Completed Tasks')
                ->state(function ($record) use ($userStats) {
                    $stat = $userStats->firstWhere('user_id', $record->id);
                    return $stat['completed_count'] ?? 0;
                })
                ->badge()
                ->color('success')
                ->alignCenter(),
            
            TextColumn::make('on_time_rate')
                ->label('On-Time Rate')
                ->state(function ($record) use ($userStats) {
                    $stat = $userStats->firstWhere('user_id', $record->id);
                    return ($stat['on_time_rate'] ?? 0) . '%';
                })
                ->badge()
                ->color(fn ($state) => match (true) {
                    floatval($state) >= 90 => 'success',
                    floatval($state) >= 70 => 'warning',
                    default => 'danger',
                })
                ->alignCenter(),
            
            TextColumn::make('on_time_count')
                ->label('On-Time Completions')
                ->state(function ($record) use ($userStats) {
                    $stat = $userStats->firstWhere('user_id', $record->id);
                    return $stat['on_time_count'] ?? 0;
                })
                ->alignCenter()
                ->toggleable(),
            
            TextColumn::make('performance_score')
                ->label('Score')
                ->state(function ($record) use ($userStats) {
                    $stat = $userStats->firstWhere('user_id', $record->id);
                    return number_format($stat['score'] ?? 0, 0);
                })
                ->badge()
                ->color('primary')
                ->alignCenter(),
        ];
    }

    public function getDescription(): ?string
    {
        return 'Ranked by completed tasks and on-time delivery rate';
    }
}
