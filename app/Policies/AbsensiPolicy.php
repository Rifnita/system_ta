<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Absensi;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbsensiPolicy
{
    use HandlesAuthorization;

    private function canWithFallback(AuthUser $authUser, string ...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($authUser->can($permission)) {
                return true;
            }
        }

        return false;
    }
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $this->canWithFallback($authUser, 'ViewAny:Absensi', 'view_any_absensi');
    }

    public function view(AuthUser $authUser, Absensi $absensi): bool
    {
        return $this->canWithFallback($authUser, 'View:Absensi', 'view_absensi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $this->canWithFallback($authUser, 'Create:Absensi', 'create_absensi');
    }

    public function update(AuthUser $authUser, Absensi $absensi): bool
    {
        if ($this->canWithFallback($authUser, 'Update:Absensi', 'update_absensi')) {
            return true;
        }

        // Izinkan user melakukan checkout absensi miliknya sendiri pada hari yang sama.
        return $this->canWithFallback($authUser, 'Create:Absensi', 'create_absensi')
            && $absensi->canCheckoutBy($authUser);
    }

    public function delete(AuthUser $authUser, Absensi $absensi): bool
    {
        return $this->canWithFallback($authUser, 'Delete:Absensi', 'delete_absensi');
    }

    public function restore(AuthUser $authUser, Absensi $absensi): bool
    {
        return $authUser->can('Restore:Absensi');
    }

    public function forceDelete(AuthUser $authUser, Absensi $absensi): bool
    {
        return $authUser->can('ForceDelete:Absensi');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Absensi');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Absensi');
    }

    public function replicate(AuthUser $authUser, Absensi $absensi): bool
    {
        return $authUser->can('Replicate:Absensi');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Absensi');
    }

}
