<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KategoriTaskResource\Pages;
use App\Filament\Resources\KategoriTaskResource\Schemas\KategoriTaskForm;
use App\Filament\Resources\KategoriTaskResource\Tables\KategoriTaskTable;
use App\Models\KategoriLaporanAktivitas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class KategoriTaskResource extends Resource
{
    protected static ?string $model = KategoriLaporanAktivitas::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Kategori Task';

    protected static ?string $modelLabel = 'Kategori';

    protected static ?string $pluralModelLabel = 'Kategori Task';

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Tugas';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Admin/supervisor yang boleh monitoring laporan aktivitas juga yang boleh mengelola kategori.
        return $user->can('view_any_laporan::aktivitas') || $user->can('ViewAny:LaporanAktivitas');
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        return (bool) ($user?->can('view_any_laporan::aktivitas') || $user?->can('ViewAny:LaporanAktivitas'));
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny();
    }

    public static function form(Schema $schema): Schema
    {
        return KategoriTaskForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KategoriTaskTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKategoriTask::route('/'),
            'create' => Pages\CreateKategoriTask::route('/create'),
            'edit' => Pages\EditKategoriTask::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
