<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'alamat',
        'is_active',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    /**
     * Get the user's profile photo URL for Filament.
     *
     * @return string|null
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_photo_url;
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        $path = $this->extractProfilePhotoPath($this->profile_photo_path);

        if (! filled($path)) {
            return asset('images/default-avatar.svg');
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');

        if (Str::startsWith($normalizedPath, 'storage/')) {
            return asset($normalizedPath);
        }

        if (Str::startsWith($normalizedPath, 'public/')) {
            $normalizedPath = Str::after($normalizedPath, 'public/');
        }

        return asset('storage/' . ltrim($normalizedPath, '/'));
    }

    public function setProfilePhotoPathAttribute(string | array | null $value): void
    {
        $normalizedPath = $this->extractProfilePhotoPath($value);

        if (blank($normalizedPath)) {
            $this->attributes['profile_photo_path'] = null;

            return;
        }

        if (Str::startsWith($normalizedPath, 'storage/')) {
            $normalizedPath = Str::after($normalizedPath, 'storage/');
        }

        if (Str::startsWith($normalizedPath, 'public/')) {
            $normalizedPath = Str::after($normalizedPath, 'public/');
        }

        $this->attributes['profile_photo_path'] = $normalizedPath;
    }

    protected function extractProfilePhotoPath(string | array | null $value): ?string
    {
        if (is_array($value)) {
            $value = collect($value)->flatten()->filter(fn ($item) => filled($item))->first();
        }

        if (blank($value)) {
            return null;
        }

        return ltrim(trim((string) $value), '/');
    }

    /**
     * Get the laporan aktivitas for the user.
     */
    public function laporanAktivitas()
    {
        return $this->hasMany(LaporanAktivitas::class);
    }

    public function transaksiKeuangan()
    {
        return $this->hasMany(TransaksiKeuangan::class);
    }
}
