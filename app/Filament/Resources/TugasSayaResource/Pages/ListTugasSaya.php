<?php

namespace App\Filament\Resources\TugasSayaResource\Pages;

use App\Filament\Resources\TugasSayaResource;
use App\Models\LaporanAktivitas;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListTugasSaya extends ListRecords
{
    protected static string $resource = TugasSayaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Task')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        $userId = Auth::id();

        return [
            'semua' => Tab::make('Semua Task')
                ->badge(fn () => LaporanAktivitas::where('user_id', $userId)->count()),

            'pending' => Tab::make('Pending')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => LaporanAktivitas::where('user_id', $userId)->where('status', 'pending')->count())
                ->badgeColor('warning'),

            'in_progress' => Tab::make('On Progress')
                ->icon('heroicon-o-arrow-path')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'in_progress'))
                ->badge(fn () => LaporanAktivitas::where('user_id', $userId)->where('status', 'in_progress')->count())
                ->badgeColor('info'),

            'completed' => Tab::make('Completed')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge(fn () => LaporanAktivitas::where('user_id', $userId)->where('status', 'completed')->count())
                ->badgeColor('success'),
        ];
    }
}
