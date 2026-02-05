<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;

class EditProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $title = 'Edit Profile';

    protected string $view = 'filament.pages.edit-profile';

    // Hide from navigation sidebar
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->data = [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'alamat' => $user->alamat,
            'profile_photo_path' => $user->profile_photo_path,
        ];
        $this->form->fill($this->data);
    }

    public function getFormColumns(): int | array
    {
        return 3;
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Profile Photo')
                ->description('Upload your profile photo')
                ->icon('heroicon-o-camera')
                ->schema([
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

            Section::make('User Details')
                ->description('Full user details')
                ->icon('heroicon-o-identification')
                ->columns(2)
                ->schema([
                    // Personal Information
                    Section::make('Personal Information')
                        ->icon('heroicon-o-user')
                        ->columns(2)
                        ->schema([
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
                                ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                    return $rule->ignore(Auth::id());
                                })
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
                        ->schema([
                            TextInput::make('username')
                                ->label('Username')
                                ->required()
                                ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                    return $rule->ignore(Auth::id());
                                })
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
                        ])
                        ->columnSpanFull(),
                ])
                ->columnSpan(2)
                ->collapsible()
                ->collapsed(false),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = Auth::user();

        // Update user data
        $user->name = $data['name'];
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->alamat = $data['alamat'];
        
        if (isset($data['profile_photo_path'])) {
            $user->profile_photo_path = $data['profile_photo_path'];
        }

        $user->save();

        Notification::make()
            ->title('Profile updated successfully')
            ->success()
            ->send();
    }
}
