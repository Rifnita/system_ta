<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

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
     * Get the user's profile photo URL for Filament.
     *
     * @return string|null
     */
    public function getFilamentAvatarUrl(): ?string
    {
        if (! filled($this->profile_photo_path)) {
            return null;
        }

        if (Str::startsWith($this->profile_photo_path, ['http://', 'https://'])) {
            return $this->profile_photo_path;
        }

        return asset('storage/' . ltrim($this->profile_photo_path, '/'));
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
