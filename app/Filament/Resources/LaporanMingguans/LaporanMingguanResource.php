<?php

namespace App\Filament\Resources\LaporanMingguans;

use App\Filament\Resources\LaporanMingguans\Pages\CreateLaporanMingguan;
use App\Filament\Resources\LaporanMingguans\Pages\EditLaporanMingguan;
use App\Filament\Resources\LaporanMingguans\Pages\ListLaporanMingguans;
use App\Filament\Resources\LaporanMingguans\Schemas\LaporanMingguanForm;
use App\Filament\Resources\LaporanMingguans\Tables\LaporanMingguansTable;
use App\Models\LaporanMingguan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LaporanMingguanResource extends Resource
{
    protected static ?string $model = LaporanMingguan::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Laporan Mingguan';

    protected static ?string $modelLabel = 'Laporan Mingguan';

    protected static ?string $pluralModelLabel = 'Laporan Mingguan';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return LaporanMingguanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LaporanMingguansTable::configure($table);
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
            'index' => ListLaporanMingguans::route('/'),
            'create' => CreateLaporanMingguan::route('/create'),
            'edit' => EditLaporanMingguan::route('/{record}/edit'),
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
