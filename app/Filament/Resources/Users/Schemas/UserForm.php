<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('profile_photo_path')
                    ->label('Foto Profil')
                    ->image()
                    ->avatar()
                    ->directory('profile-photos')
                    ->visibility('public')
                    ->imageEditor()
                    ->circleCropper()
                    ->maxSize(2048)
                    ->columnSpanFull()
                    ->helperText('Upload foto profil dengan maksimal ukuran 2MB'),
                
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255)
                    ->prefixIcon('heroicon-o-user')
                    ->placeholder('Masukkan nama lengkap')
                    ->columnSpan(1),
                
                TextInput::make('username')
                    ->label('Username')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->prefixIcon('heroicon-o-at-symbol')
                    ->placeholder('Masukkan username')
                    ->alphaDash()
                    ->helperText('Username hanya boleh berisi huruf, angka, dash, dan underscore')
                    ->columnSpan(1),
                
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->prefixIcon('heroicon-o-envelope')
                    ->placeholder('nama@example.com')
                    ->helperText('Email akan digunakan untuk verifikasi dan reset password')
                    ->columnSpan(1),
                
                TextInput::make('password')
                    ->password()
                    ->required(fn ($context) => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->label('Password')
                    ->maxLength(255)
                    ->prefixIcon('heroicon-o-lock-closed')
                    ->placeholder('Minimal 8 karakter')
                    ->minLength(8)
                    ->revealable()
                    ->helperText(fn ($context) => $context === 'edit' ? 'Kosongkan jika tidak ingin mengubah password' : 'Minimal 8 karakter')
                    ->columnSpan(1),
                
                Select::make('roles')
                    ->label('Role / Peran')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->prefixIcon('heroicon-o-shield-check')
                    ->placeholder('Pilih role untuk pengguna')
                    ->helperText('Anda dapat memilih lebih dari satu role')
                    ->columnSpan(2),
                
                Textarea::make('alamat')
                    ->label('Alamat')
                    ->rows(3)
                    ->maxLength(65535)
                    ->placeholder('Masukkan alamat lengkap')
                    ->columnSpanFull(),
                
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required()
                    ->helperText('Nonaktifkan untuk melarang pengguna login')
                    ->inline(false)
                    ->columnSpan(1),
            ])
            ->columns(2);
    }
}
