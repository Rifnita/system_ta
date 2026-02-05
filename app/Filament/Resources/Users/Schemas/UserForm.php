<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // Avatar Section - Sidebar
                Section::make('Foto Profil')
                    ->description('Upload foto profil pengguna')
                    ->icon('heroicon-o-camera')
                    ->components([
                        FileUpload::make('profile_photo_path')
                            ->label('')
                            ->image()
                            ->avatar()
                            ->directory('profile-photos')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->imageEditor()
                            ->circleCropper()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Maks. 2MB (JPG, PNG, WEBP)')
                            ->alignCenter(),
                    ])
                    ->columnSpan(1)
                    ->collapsible()
                    ->collapsed(false),

                // Main Form - Takes 2/3 width
                Section::make('Data Pengguna')
                    ->description('Informasi lengkap pengguna sistem')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->components([
                        // Personal Information
                        Section::make('Informasi Pribadi')
                            ->icon('heroicon-o-user')
                            ->columns(2)
                            ->components([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Masukkan nama lengkap')
                                    ->autocomplete('name')
                                    ->prefixIcon('heroicon-o-user')
                                    ->columnSpan(2),

                                TextInput::make('email')
                                    ->label('Alamat Email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('nama@example.com')
                                    ->autocomplete('email')
                                    ->prefixIcon('heroicon-o-envelope')
                                    ->helperText('Email untuk verifikasi & reset password')
                                    ->columnSpan(2),
                            ])
                            ->columnSpanFull(),

                        // Work Information
                        Section::make('Informasi Kepegawaian')
                            ->icon('heroicon-o-briefcase')
                            ->columns(2)
                            ->components([
                                TextInput::make('username')
                                    ->label('Username')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('Masukkan username')
                                    ->prefixIcon('heroicon-o-at-symbol')
                                    ->helperText('Hanya huruf, angka, dash dan underscore')
                                    ->alphaDash()
                                    ->columnSpan(1),

                                TextInput::make('alamat')
                                    ->label('Alamat')
                                    ->maxLength(65535)
                                    ->placeholder('Masukkan alamat')
                                    ->prefixIcon('heroicon-o-map-pin')
                                    ->columnSpan(1),

                                Select::make('roles')
                                    ->label('Role / Jabatan')
                                    ->relationship('roles', 'name')
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->prefixIcon('heroicon-o-shield-check')
                                    ->helperText('Pilih satu role untuk pengguna')
                                    ->placeholder('Pilih role')
                                    ->native(false)
                                    ->columnSpan(2),
                            ])
                            ->columnSpanFull(),

                        // Security
                        Section::make('Keamanan Akun')
                            ->icon('heroicon-o-lock-closed')
                            ->columns(2)
                            ->components([
                                TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->revealable()
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn ($context) => $context === 'create')
                                    ->minLength(8)
                                    ->maxLength(255)
                                    ->placeholder('Minimal 8 karakter')
                                    ->prefixIcon('heroicon-o-key')
                                    ->helperText('Min. 8 karakter')
                                    ->confirmed()
                                    ->validationAttribute('password')
                                    ->visible(fn ($context) => $context === 'create')
                                    ->columnSpan(1),

                                TextInput::make('password_confirmation')
                                    ->label('Konfirmasi Password')
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(false)
                                    ->required(fn ($context) => $context === 'create')
                                    ->placeholder('Ketik ulang password')
                                    ->prefixIcon('heroicon-o-key')
                                    ->visible(fn ($context) => $context === 'create')
                                    ->columnSpan(1),

                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->helperText('Aktifkan untuk memberikan akses ke sistem')
                                    ->default(true)
                                    ->inline(false)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->columnSpan(fn ($context) => $context === 'create' ? 2 : 1),

                                Placeholder::make('email_verification_status')
                                    ->label('Verifikasi Email')
                                    ->content(function ($record) {
                                        if (!$record) {
                                            return '📧 Email verifikasi akan dikirim otomatis';
                                        }
                                        
                                        if ($record->hasVerifiedEmail()) {
                                            return '✅ Terverifikasi (' . 
                                                   $record->email_verified_at->format('d M Y H:i') . ')';
                                        }
                                        
                                        return '⏳ Menunggu verifikasi';
                                    })
                                    ->columnSpan(1)
                                    ->visible(fn ($context) => $context === 'edit'),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(2)
                    ->collapsible()
                    ->collapsed(false),

                // Metadata Section (Only visible on edit) - Full Width
                Section::make('Informasi Sistem')
                    ->description('Riwayat dan metadata akun')
                    ->icon('heroicon-o-information-circle')
                    ->columns(3)
                    ->components([
                        Placeholder::make('created_at')
                            ->label('Dibuat Pada')
                            ->content(fn ($record): string => $record?->created_at?->format('d M Y, H:i') ?? '-')
                            ->columnSpan(1),

                        Placeholder::make('updated_at')
                            ->label('Terakhir Diupdate')
                            ->content(fn ($record): string => $record?->updated_at?->diffForHumans() ?? '-')
                            ->columnSpan(1),

                        Placeholder::make('email_verified_at')
                            ->label('Email Diverifikasi')
                            ->content(fn ($record): string => 
                                $record?->email_verified_at 
                                    ? '✅ ' . $record->email_verified_at->format('d M Y, H:i')
                                    : '⏳ Belum diverifikasi'
                            )
                            ->columnSpan(1),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn ($context) => $context === 'edit'),
            ]);
    }
}
