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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class TugasResource extends Resource
{
    protected static ?string $model = LaporanAktivitas::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Manajemen Tugas';

    protected static ?string $modelLabel = 'Tugas';

    protected static ?string $pluralModelLabel = 'Tugas';

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Tugas';

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
        return TugasForm::configure($schema);
    }

    public static function canCreate(): bool
    {
        // Semua user bisa create daily task untuk diri mereka sendiri
        return true;
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
