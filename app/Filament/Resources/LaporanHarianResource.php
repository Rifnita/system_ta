<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanAktivitasResource\Schemas\LaporanAktivitasForm;
use App\Filament\Resources\LaporanHarianResource\Pages;
use App\Filament\Resources\LaporanHarianResource\Tables\LaporanHarianTable;
use App\Models\LaporanAktivitas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class LaporanHarianResource extends Resource
{
    protected static ?string $model = LaporanAktivitas::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Task Harian Saya';

    protected static ?string $modelLabel = 'Task Harian';

    protected static ?string $pluralModelLabel = 'Task Harian';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

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
        return LaporanAktivitasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LaporanHarianTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanHarian::route('/'),
            'create' => Pages\CreateLaporanHarian::route('/create'),
            'edit' => Pages\EditLaporanHarian::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Menampilkan hanya laporan milik user yang sedang login
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }
}
