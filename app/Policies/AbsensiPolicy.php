<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Absensi;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbsensiPolicy
{
    use HandlesAuthorization;

    private function isAdmin(AuthUser $authUser): bool
    {
        return method_exists($authUser, 'hasRole')
            && ($authUser->hasRole('super_admin') || $authUser->hasRole('admin'));
    }
    
    public function viewAny(AuthUser $authUser): bool
    {
        return true;
    }

    public function view(AuthUser $authUser, Absensi $absensi): bool
    {
        return $this->isAdmin($authUser) || (int) $absensi->user_id === (int) $authUser->id;
    }

    public function create(AuthUser $authUser): bool
    {
        return true;
    }

    public function update(AuthUser $authUser, Absensi $absensi): bool
    {
        return $this->isAdmin($authUser) || (int) $absensi->user_id === (int) $authUser->id;
    }

    public function delete(AuthUser $authUser, Absensi $absensi): bool
    {
        return $this->isAdmin($authUser);
    }

    public function restore(AuthUser $authUser, Absensi $absensi): bool
    {
        return $this->isAdmin($authUser);
    }

    public function forceDelete(AuthUser $authUser, Absensi $absensi): bool
    {
        return $this->isAdmin($authUser);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $this->isAdmin($authUser);
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $this->isAdmin($authUser);
    }

    public function replicate(AuthUser $authUser, Absensi $absensi): bool
    {
        return $this->isAdmin($authUser);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $this->isAdmin($authUser);
    }

}