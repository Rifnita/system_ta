<?php

namespace App\Filament\Resources\KategoriTransaksiKeuangans;

use App\Filament\Resources\KategoriTransaksiKeuangans\Pages\CreateKategoriTransaksiKeuangan;
use App\Filament\Resources\KategoriTransaksiKeuangans\Pages\EditKategoriTransaksiKeuangan;
use App\Filament\Resources\KategoriTransaksiKeuangans\Pages\ListKategoriTransaksiKeuangans;
use App\Filament\Resources\KategoriTransaksiKeuangans\Schemas\KategoriTransaksiKeuanganForm;
use App\Filament\Resources\KategoriTransaksiKeuangans\Tables\KategoriTransaksiKeuangansTable;
use App\Models\KategoriTransaksiKeuangan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class KategoriTransaksiKeuanganResource extends Resource
{
    protected static ?string $model = KategoriTransaksiKeuangan::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Kategori Transaksi';

    protected static ?string $modelLabel = 'Kategori Transaksi';

    protected static ?string $pluralModelLabel = 'Kategori Transaksi';

    protected static ?int $navigationSort = 1;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Keuangan';

    public static function form(Schema $schema): Schema
    {
        return KategoriTransaksiKeuanganForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KategoriTransaksiKeuangansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKategoriTransaksiKeuangans::route('/'),
            'create' => CreateKategoriTransaksiKeuangan::route('/create'),
            'edit' => EditKategoriTransaksiKeuangan::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
