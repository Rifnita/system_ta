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

    protected static ?string $navigationLabel = 'Task Management';

    protected static ?string $modelLabel = 'Task';

    protected static ?string $pluralModelLabel = 'Tasks';

    protected static string|UnitEnum|null $navigationGroup = 'Task Management';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // All users can access the menu to manage their own tasks
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return TugasForm::configure($schema);
    }

    public static function canCreate(): bool
    {
        // All users can create daily tasks for themselves
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

        // Admin/Supervisor can view all tasks, regular users can only view their own tasks
        if ($user && !$user->hasAnyRole(['super_admin', 'admin'])) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }
}
