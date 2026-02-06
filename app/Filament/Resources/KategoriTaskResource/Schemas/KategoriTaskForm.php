<?php

namespace App\Filament\Resources\KategoriTaskResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KategoriTaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category')
                    ->components([
                        Forms\Components\TextInput::make('nama')
                            ->label('Category Name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),
            ]);
    }
}
