<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LaporanAktivitas;
use Illuminate\Auth\Access\HandlesAuthorization;

class LaporanAktivitasPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LaporanAktivitas');
    }

    public function view(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        return $authUser->can('View:LaporanAktivitas');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LaporanAktivitas');
    }

    public function update(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        return $authUser->can('Update:LaporanAktivitas');
    }

    public function delete(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        return $authUser->can('Delete:LaporanAktivitas');
    }

    public function restore(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        return $authUser->can('Restore:LaporanAktivitas');
    }

    public function forceDelete(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        return $authUser->can('ForceDelete:LaporanAktivitas');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LaporanAktivitas');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LaporanAktivitas');
    }

    public function replicate(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        return $authUser->can('Replicate:LaporanAktivitas');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LaporanAktivitas');
    }

}