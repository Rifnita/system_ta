<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make('Foto Profil')
                            ->compact()
                            ->schema([
                                FileUpload::make('profile_photo_path')
                                    ->label('')
                                    ->image()
                                    ->avatar()
                                    ->directory('profile-photos')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->maxSize(2048)
                                    ->helperText('Upload foto profil (maks. 2MB).'),
                            ])
                            ->columnSpan(1),

                        Section::make('Data Pengguna')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-user')
                                    ->placeholder('Masukkan nama lengkap'),

                                TextInput::make('username')
                                    ->label('Username')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-at-symbol')
                                    ->placeholder('Masukkan username')
                                    ->alphaDash()
                                    ->helperText('Huruf/angka/dash/underscore'),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-envelope')
                                    ->placeholder('nama@example.com')
                                    ->helperText('Email untuk verifikasi & reset password'),

                                TextInput::make('password')
                                    ->password()
                                    ->visible(fn ($context) => $context === 'create')
                                    ->required(fn ($context) => $context === 'create')
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->label('Password')
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-lock-closed')
                                    ->placeholder('Minimal 8 karakter')
                                    ->minLength(8)
                                    ->revealable()
                                    ->helperText('Minimal 8 karakter'),

                                Select::make('roles')
                                    ->label('Role / Peran')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->prefixIcon('heroicon-o-shield-check')
                                    ->placeholder('Pilih role untuk pengguna')
                                    ->helperText('Bisa pilih lebih dari satu'),

                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->required()
                                    ->helperText('Nonaktifkan untuk melarang pengguna login')
                                    ->inline(false),

                                Textarea::make('alamat')
                                    ->label('Alamat')
                                    ->rows(3)
                                    ->maxLength(65535)
                                    ->placeholder('Masukkan alamat lengkap'),
                            ])
                            ->columns(1)
                            ->columnSpan(2),
                    ]),
            ])
            ->columns(1);
    }
}
