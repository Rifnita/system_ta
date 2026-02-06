<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanAktivitasResource\Pages;
use App\Filament\Resources\LaporanAktivitasResource\Schemas\LaporanAktivitasForm;
use App\Filament\Resources\LaporanAktivitasResource\Tables\LaporanAktivitasTable;
use App\Models\LaporanAktivitas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class LaporanAktivitasResource extends Resource
{
    protected static ?string $model = LaporanAktivitas::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Daily Task';

    protected static ?string $modelLabel = 'Daily Task';

    protected static ?string $pluralModelLabel = 'Daily Tasks';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Semua user bisa akses menu untuk manage task mereka sendiri
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return LaporanAktivitasForm::configure($schema);
    }

    public static function canCreate(): bool
    {
        // Semua user bisa create daily task untuk diri mereka sendiri
        return true;
    }

    public static function table(Table $table): Table
    {
        return LaporanAktivitasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanAktivitas::route('/'),
            'create' => Pages\CreateLaporanAktivitas::route('/create'),
            'view' => Pages\ViewLaporanAktivitas::route('/{record}'),
            'edit' => Pages\EditLaporanAktivitas::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // Admin/Supervisor bisa lihat semua task, user biasa hanya lihat task mereka
        if ($user && !$user->hasAnyRole(['super_admin', 'admin'])) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }
}
