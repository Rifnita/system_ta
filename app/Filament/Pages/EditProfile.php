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

    protected static ?string $title = 'Ubah Profil';

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
            Section::make('Foto Profil')
                ->description('Unggah foto profil Anda')
                ->icon('heroicon-o-camera')
                ->schema([
                    FileUpload::make('profile_photo_path')
                        ->label('')
                        ->image()
                        ->avatar()
                        ->disk('public')
                        ->directory('profile-photos')
                        ->visibility('public')
                        ->maxSize(2048)
                        ->imageEditor()
                        ->circleCropper()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->helperText('Maks 2MB (JPG, PNG, WEBP)')
                        ->alignCenter(),
                ])
                ->columnSpan(1)
                ->collapsible()
                ->collapsed(false),

            Section::make('Detail Pengguna')
                ->description('Detail lengkap pengguna')
                ->icon('heroicon-o-identification')
                ->columns(2)
                ->schema([
                    // Personal Information
                    Section::make('Informasi Pribadi')
                        ->icon('heroicon-o-user')
                        ->columns(2)
                        ->schema([
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
                                ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                    return $rule->ignore(Auth::id());
                                })
                                ->maxLength(255)
                                ->placeholder('name@example.com')
                                ->autocomplete('email')
                                ->prefixIcon('heroicon-o-envelope')
                                ->helperText('Email untuk verifikasi dan reset kata sandi')
                                ->columnSpan(2),
                        ])
                        ->columnSpanFull(),

                    // Work Information
                    Section::make('Informasi Kerja')
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
                                ->placeholder('Masukkan username')
                                ->prefixIcon('heroicon-o-at-symbol')
                                ->helperText('Hanya huruf, angka, tanda hubung, dan garis bawah')
                                ->alphaDash()
                                ->columnSpan(1),

                            TextInput::make('alamat')
                                ->label('Alamat')
                                ->maxLength(65535)
                                ->placeholder('Masukkan alamat')
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
            ->title('Profil berhasil diperbarui')
            ->success()
            ->send();
    }
}
