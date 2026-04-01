<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TugasResource\Pages;
use App\Filament\Resources\TugasResource\Schemas\TugasForm;
use App\Filament\Resources\TugasResource\Tables\TugasTable;
use App\Models\LaporanAktivitas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class TugasResource extends Resource
{
    protected static ?string $model = LaporanAktivitas::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Daftar Tugas';

    protected static ?string $modelLabel = 'Daftar Tugas';

    protected static ?string $pluralModelLabel = 'Daftar Tugas';

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Tugas';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && $user->can('ViewAny:TugasResource');
    }

    public static function form(Schema $schema): Schema
    {
        return TugasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TugasTable::configure($table);
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
            'index' => Pages\ListTugas::route('/'),
            'create' => Pages\CreateTugas::route('/create'),
            'view' => Pages\ViewTugas::route('/{record}'),
            'edit' => Pages\EditTugas::route('/{record}/edit'),
        ];
    }
}
