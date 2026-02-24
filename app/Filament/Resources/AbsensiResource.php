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
    private const PERMISSION_MAP = [
        'view_any' => ['view_any_absensi', 'ViewAny:Absensi'],
        'view' => ['view_absensi', 'View:Absensi'],
        'create' => ['create_absensi', 'Create:Absensi'],
        'update' => ['update_absensi', 'Update:Absensi'],
        'delete' => ['delete_absensi', 'Delete:Absensi'],
    ];

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
        $today = Absensi::whereDate('tanggal', today())->count();
        return $today > 0 ? (string) $today : null;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Non-admin hanya bisa lihat absensi sendiri
        if (!static::canDo('view_any')) {
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

        foreach (static::PERMISSION_MAP[$ability] ?? [] as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }
}
