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
                Section::make('Profile Photo')
                    ->description('Upload a user profile photo')
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
                            ->helperText('Max 2MB (JPG, PNG, WEBP)')
                            ->alignCenter(),
                    ])
                    ->columnSpan(1)
                    ->collapsible()
                    ->collapsed(false),

                // Main Form - Takes 2/3 width
                Section::make('User Details')
                    ->description('Full user details')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->components([
                        // Personal Information
                        Section::make('Personal Information')
                            ->icon('heroicon-o-user')
                            ->columns(2)
                            ->components([
                                TextInput::make('name')
                                    ->label('Full Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter full name')
                                    ->autocomplete('name')
                                    ->prefixIcon('heroicon-o-user')
                                    ->columnSpan(2),

                                TextInput::make('email')
                                    ->label('Email Address')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('name@example.com')
                                    ->autocomplete('email')
                                    ->prefixIcon('heroicon-o-envelope')
                                    ->helperText('Email for verification and password reset')
                                    ->columnSpan(2),
                            ])
                            ->columnSpanFull(),

                        // Work Information
                        Section::make('Work Information')
                            ->icon('heroicon-o-briefcase')
                            ->columns(2)
                            ->components([
                                TextInput::make('username')
                                    ->label('Username')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('Enter username')
                                    ->prefixIcon('heroicon-o-at-symbol')
                                    ->helperText('Letters, numbers, dashes, and underscores only')
                                    ->alphaDash()
                                    ->columnSpan(1),

                                TextInput::make('alamat')
                                    ->label('Address')
                                    ->maxLength(65535)
                                    ->placeholder('Enter address')
                                    ->prefixIcon('heroicon-o-map-pin')
                                    ->columnSpan(1),

                                Select::make('roles')
                                    ->label('Role / Position')
                                    ->relationship('roles', 'name')
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->prefixIcon('heroicon-o-shield-check')
                                    ->helperText('Select a role for the user')
                                    ->placeholder('Select role')
                                    ->native(false)
                                    ->columnSpan(2),
                            ])
                            ->columnSpanFull(),

                        // Security
                        Section::make('Account Security')
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
                                    ->placeholder('At least 8 characters')
                                    ->prefixIcon('heroicon-o-key')
                                    ->helperText('Min. 8 characters')
                                    ->confirmed()
                                    ->validationAttribute('password')
                                    ->visible(fn ($context) => $context === 'create')
                                    ->columnSpan(1),

                                TextInput::make('password_confirmation')
                                    ->label('Confirm Password')
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(false)
                                    ->required(fn ($context) => $context === 'create')
                                    ->placeholder('Re-enter password')
                                    ->prefixIcon('heroicon-o-key')
                                    ->visible(fn ($context) => $context === 'create')
                                    ->columnSpan(1),

                                Toggle::make('is_active')
                                    ->label('Active Status')
                                    ->helperText('Enable to grant system access')
                                    ->default(true)
                                    ->inline(false)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->columnSpan(fn ($context) => $context === 'create' ? 2 : 1),

                                Placeholder::make('email_verification_status')
                                    ->label('Email Verification')
                                    ->content(function ($record) {
                                        if (!$record) {
                                            return '📧 Verification email will be sent automatically';
                                        }
                                        
                                        if ($record->hasVerifiedEmail()) {
                                            return '✅ Verified (' . 
                                                   $record->email_verified_at->format('d M Y H:i') . ')';
                                        }
                                        
                                        return '⏳ Pending verification';
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
                Section::make('System Information')
                    ->description('Account history and metadata')
                    ->icon('heroicon-o-information-circle')
                    ->columns(3)
                    ->components([
                        Placeholder::make('created_at')
                            ->label('Created At')
                            ->content(fn ($record): string => $record?->created_at?->format('d M Y, H:i') ?? '-')
                            ->columnSpan(1),

                        Placeholder::make('updated_at')
                            ->label('Last Updated')
                            ->content(fn ($record): string => $record?->updated_at?->diffForHumans() ?? '-')
                            ->columnSpan(1),

                        Placeholder::make('email_verified_at')
                            ->label('Email Verified')
                            ->content(fn ($record): string => 
                                $record?->email_verified_at 
                                    ? '✅ ' . $record->email_verified_at->format('d M Y, H:i')
                                    : '⏳ Not verified'
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
