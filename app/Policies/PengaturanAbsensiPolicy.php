<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PengaturanAbsensi;
use Illuminate\Auth\Access\HandlesAuthorization;

class PengaturanAbsensiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PengaturanAbsensiResource');
    }

    public function view(AuthUser $authUser, PengaturanAbsensi $pengaturanAbsensi): bool
    {
        return $authUser->can('View:PengaturanAbsensiResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PengaturanAbsensiResource');
    }

    public function update(AuthUser $authUser, PengaturanAbsensi $pengaturanAbsensi): bool
    {
        return $authUser->can('Update:PengaturanAbsensiResource');
    }

    public function delete(AuthUser $authUser, PengaturanAbsensi $pengaturanAbsensi): bool
    {
        return $authUser->can('Delete:PengaturanAbsensiResource');
    }

    public function restore(AuthUser $authUser, PengaturanAbsensi $pengaturanAbsensi): bool
    {
        return $authUser->can('Restore:PengaturanAbsensiResource');
    }

    public function forceDelete(AuthUser $authUser, PengaturanAbsensi $pengaturanAbsensi): bool
    {
        return $authUser->can('ForceDelete:PengaturanAbsensiResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PengaturanAbsensiResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PengaturanAbsensiResource');
    }

    public function replicate(AuthUser $authUser, PengaturanAbsensi $pengaturanAbsensi): bool
    {
        return $authUser->can('Replicate:PengaturanAbsensiResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PengaturanAbsensiResource');
    }

}