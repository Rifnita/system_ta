<?php

namespace App\Filament\Resources\TransaksiKeuangans;

use App\Filament\Resources\TransaksiKeuangans\Pages\CreateTransaksiKeuangan;
use App\Filament\Resources\TransaksiKeuangans\Pages\EditTransaksiKeuangan;
use App\Filament\Resources\TransaksiKeuangans\Pages\ListTransaksiKeuangans;
use App\Filament\Resources\TransaksiKeuangans\Schemas\TransaksiKeuanganForm;
use App\Filament\Resources\TransaksiKeuangans\Tables\TransaksiKeuangansTable;
use App\Models\TransaksiKeuangan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class TransaksiKeuanganResource extends Resource
{
    private const PERMISSION_MAP = [
        'view_any' => ['view_any_transaksi_keuangan', 'ViewAny:TransaksiKeuangan'],
    ];

    protected static ?string $model = TransaksiKeuangan::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Transaksi Keuangan';

    protected static ?string $modelLabel = 'Transaksi Keuangan';

    protected static ?string $pluralModelLabel = 'Transaksi Keuangan';

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Keuangan';

    public static function form(Schema $schema): Schema
    {
        return TransaksiKeuanganForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransaksiKeuangansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransaksiKeuangans::route('/'),
            'create' => CreateTransaksiKeuangan::route('/create'),
            'edit' => EditTransaksiKeuangan::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (! static::canViewAnyRecords()) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    private static function canViewAnyRecords(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        foreach (self::PERMISSION_MAP['view_any'] as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }
}
