<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource as ShieldRoleResource;
use UnitEnum;

class RoleResource extends ShieldRoleResource
{
    protected static string|UnitEnum|null $navigationGroup = 'User Management';
    
    protected static ?int $navigationSort = 10;
    
    public static function getNavigationGroup(): ?string
    {
        return 'User Management';
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
