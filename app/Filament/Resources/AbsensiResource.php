<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsensiResource\Pages;
use App\Filament\Resources\AbsensiResource\Schemas\AbsensiForm;
use App\Filament\Resources\AbsensiResource\Tables\AbsensiTable;
use App\Models\Absensi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Absensi';

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Absensi';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AbsensiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AbsensiTable::configure($table);
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
            'index' => Pages\ListAbsensis::route('/'),
            'create' => Pages\CreateAbsensi::route('/create'),
            'edit' => Pages\EditAbsensi::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        $today = Absensi::query()
            ->whereDate('tanggal', today())
            ->when(
                ! $user || ! ($user->hasRole('super_admin') || $user->hasRole('admin')),
                fn (Builder $query): Builder => $query->where('user_id', Auth::id()),
            )
            ->count();

        return $today > 0 ? (string) $today : null;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        
        // User non-admin hanya bisa melihat absensi miliknya sendiri.
        if (! $user || !($user->hasRole('super_admin') || $user->hasRole('admin'))) {
            $query->where('user_id', Auth::id());
        }
        
        return $query;
    }

    public static function canDo(string $ability): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (in_array($ability, ['view_any', 'view', 'create', 'update'], true)) {
            return true;
        }

        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }
}
