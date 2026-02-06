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
                Section::make('Kategori')
                    ->components([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),
            ]);
    }
}
