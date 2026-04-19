<?php

namespace App\Filament\Resources\PengajuanCutis;

use App\Filament\Resources\PengajuanCutis\Pages\CreatePengajuanCuti;
use App\Filament\Resources\PengajuanCutis\Pages\EditPengajuanCuti;
use App\Filament\Resources\PengajuanCutis\Pages\ListPengajuanCutis;
use App\Filament\Resources\PengajuanCutis\Schemas\PengajuanCutiForm;
use App\Filament\Resources\PengajuanCutis\Tables\PengajuanCutisTable;
use App\Models\PengajuanCuti;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class PengajuanCutiResource extends Resource
{
    protected static ?string $model = PengajuanCuti::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Pengajuan Cuti';

    protected static ?string $modelLabel = 'Pengajuan Cuti';

    protected static ?string $pluralModelLabel = 'Pengajuan Cuti';

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Absensi';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PengajuanCutiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengajuanCutisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengajuanCutis::route('/'),
            'create' => CreatePengajuanCuti::route('/create'),
            'edit' => EditPengajuanCuti::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (! $user || ! (method_exists($user, 'hasRole') && ($user->hasRole('super_admin') || $user->hasRole('admin')))) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }
}
