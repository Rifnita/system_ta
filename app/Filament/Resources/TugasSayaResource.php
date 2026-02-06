<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TugasResource\Schemas\TugasForm;
use App\Filament\Resources\TugasSayaResource\Pages;
use App\Filament\Resources\TugasSayaResource\Tables\TugasSayaTable;
use App\Models\LaporanAktivitas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class TugasSayaResource extends Resource
{
    protected static ?string $model = LaporanAktivitas::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Tugas Saya';

    protected static ?string $modelLabel = 'Tugas';

    protected static ?string $pluralModelLabel = 'Tugas';

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Tugas';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Tampilkan menu jika user punya permission terkait (biarkan Shield yang mengatur).
        return $user->can('view_laporan::aktivitas')
            || $user->can('create_laporan::aktivitas')
            || $user->can('update_laporan::aktivitas')
            || $user->can('view_any_laporan::aktivitas')
            || $user->can('View:LaporanAktivitas')
            || $user->can('Create:LaporanAktivitas')
            || $user->can('Update:LaporanAktivitas')
            || $user->can('ViewAny:LaporanAktivitas');
    }

    public static function form(Schema $schema): Schema
    {
        return TugasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TugasSayaTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTugasSaya::route('/'),
            'create' => Pages\CreateTugasSaya::route('/create'),
            'edit' => Pages\EditTugasSaya::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Menampilkan hanya laporan milik user yang sedang login
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }
}
