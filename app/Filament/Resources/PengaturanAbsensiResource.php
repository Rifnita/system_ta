<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengaturanAbsensiResource\Pages;
use App\Filament\Resources\PengaturanAbsensiResource\Schemas\PengaturanAbsensiForm;
use App\Filament\Resources\PengaturanAbsensiResource\Tables\PengaturanAbsensiTable;
use App\Models\PengaturanAbsensi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class PengaturanAbsensiResource extends Resource
{
    protected static ?string $model = PengaturanAbsensi::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan Absensi';

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Absensi';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return PengaturanAbsensiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengaturanAbsensiTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengaturanAbsensis::route('/'),
            'create' => Pages\CreatePengaturanAbsensi::route('/create'),
            'edit' => Pages\EditPengaturanAbsensi::route('/{record}/edit'),
        ];
    }
}
