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

class LaporanAktivitasResource extends Resource
{
    protected static ?string $model = LaporanAktivitas::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Laporan Aktivitas';

    protected static ?string $modelLabel = 'Laporan Aktivitas';

    protected static ?string $pluralModelLabel = 'Laporan Aktivitas';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Menu ini khusus admin/supervisor untuk monitoring & pengelolaan data.
        return $user->can('view_any_laporan::aktivitas') || $user->can('ViewAny:LaporanAktivitas');
    }

    public static function form(Schema $schema): Schema
    {
        return LaporanAktivitasForm::configure($schema);
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
            'edit' => Pages\EditLaporanAktivitas::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // If user doesn't have view_any permission, only show their own records
        if (!Auth::user()->can('view_any_laporan::aktivitas')) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }
}
