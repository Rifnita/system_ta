<?php

namespace App\Filament\Resources\Proyeks;

use App\Filament\Resources\Proyeks\Pages\CreateProyek;
use App\Filament\Resources\Proyeks\Pages\EditProyek;
use App\Filament\Resources\Proyeks\Pages\ListProyeks;
use App\Filament\Resources\Proyeks\Schemas\ProyekForm;
use App\Filament\Resources\Proyeks\Tables\ProyeksTable;
use App\Models\Proyek;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProyekResource extends Resource
{
    protected static ?string $model = Proyek::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Master Proyek';

    protected static ?string $modelLabel = 'Proyek';

    protected static ?string $pluralModelLabel = 'Proyek';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nama_proyek';

    public static function form(Schema $schema): Schema
    {
        return ProyekForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProyeksTable::configure($table);
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
            'index' => ListProyeks::route('/'),
            'create' => CreateProyek::route('/create'),
            'edit' => EditProyek::route('/{record}/edit'),
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
