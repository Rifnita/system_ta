<?php

namespace App\Filament\Widgets;

use App\Models\LaporanAktivitas;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecentTasksWidget extends BaseWidget
{
    protected static ?int $sort = 21;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Personal tasks history - only for staff/users
        $user = Auth::user();
        return $user && !$user->hasAnyRole(['super_admin', 'panel_user']);
    }

    public function getHeading(): ?string
    {
        return 'Recent Tasks Activity';
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
                    ->label('Task Title')
                    ->searchable()
                    ->weight('bold')
                    ->wrap()
                    ->limit(40),
                
                TextColumn::make('tanggal_aktivitas')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),
                
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
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state ?? 'Pending'))),
                
                TextColumn::make('is_priority')
                    ->label('Priority')
                    ->badge()
                    ->color(fn ($state) => $state ? 'danger' : 'gray')
                    ->formatStateUsing(fn ($state) => $state ? 'High' : 'Normal'),
                
                TextColumn::make('durasi')
                    ->label('Duration')
                    ->toggleable(),
                
                TextColumn::make('lokasi')
                    ->label('Location')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_aktivitas', 'desc')
            ->striped()
            ->paginated(false);
    }
}
