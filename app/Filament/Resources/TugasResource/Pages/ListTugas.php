<?php

namespace App\Filament\Resources\TugasResource\Pages;

use App\Filament\Resources\TugasResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListTugas extends ListRecords
{
    protected static string $resource = TugasResource::class;

    public function getTitle(): string | Htmlable
    {
        return 'Daftar Tugas';
    }

    public function getHeading(): string | Htmlable
    {
        return 'Daftar Tugas';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_pdf')
                ->label('Ekspor PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->modalHeading('Ekspor Laporan Aktivitas (PDF)')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->native(false)
                        ->default(now()->startOfMonth())
                        ->maxDate(fn ($get) => $get('end_date'))
                        ->required(),

                    DatePicker::make('end_date')
                        ->label('Tanggal Selesai')
                        ->native(false)
                        ->default(now())
                        ->minDate(fn ($get) => $get('start_date'))
                        ->required(),

                    Select::make('user_id')
                        ->label('Pegawai (opsional)')
                        ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->visible(fn () => TugasResource::canViewAny()),
                ])
                ->action(function (array $data) {
                    if (! TugasResource::canViewAny()) {
                        unset($data['user_id']);
                    }

                    // Livewire tidak bisa mengirim binary PDF langsung dalam response JSON.
                    // Jadi kita redirect ke route download.
                    return redirect()->route('admin.laporan-aktivitas.export.pdf', array_filter([
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
                        'user_id' => $data['user_id'] ?? null,
                    ], fn ($value) => $value !== null && $value !== ''));
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Tugas')
                ->badge(fn () => \App\Models\LaporanAktivitas::count()),
            
            'pending' => Tab::make('Belum Dimulai')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => \App\Models\LaporanAktivitas::where('status', 'pending')->count())
                ->badgeColor('warning'),
            
            'in_progress' => Tab::make('Sedang Dikerjakan')
                ->icon('heroicon-o-arrow-path')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'in_progress'))
                ->badge(fn () => \App\Models\LaporanAktivitas::where('status', 'in_progress')->count())
                ->badgeColor('info'),
            
            'completed' => Tab::make('Selesai')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge(fn () => \App\Models\LaporanAktivitas::where('status', 'completed')->count())
                ->badgeColor('success'),
        ];
    }
}
