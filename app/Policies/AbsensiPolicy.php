<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Absensi;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbsensiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AbsensiResource');
    }

    public function view(AuthUser $authUser, Absensi $absensi): bool
    {
        return $authUser->can('View:AbsensiResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AbsensiResource');
    }

    public function update(AuthUser $authUser, Absensi $absensi): bool
    {
        return $authUser->can('Update:AbsensiResource');
    }

    public function delete(AuthUser $authUser, Absensi $absensi): bool
    {
        return $authUser->can('Delete:AbsensiResource');
    }

    public function restore(AuthUser $authUser, Absensi $absensi): bool
    {
        return $authUser->can('Restore:AbsensiResource');
    }

    public function forceDelete(AuthUser $authUser, Absensi $absensi): bool
    {
        return $authUser->can('ForceDelete:AbsensiResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AbsensiResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AbsensiResource');
    }

    public function replicate(AuthUser $authUser, Absensi $absensi): bool
    {
        return $authUser->can('Replicate:AbsensiResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AbsensiResource');
    }

}