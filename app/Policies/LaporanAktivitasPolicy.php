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
        return $authUser->can('ViewAny:TugasSayaResource');
    }

    public function view(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        return $authUser->can('View:TugasSayaResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TugasSayaResource');
    }

    public function update(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        return $authUser->can('Update:TugasSayaResource');
    }

    public function delete(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        return $authUser->can('Delete:TugasSayaResource');
    }

    public function restore(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        return $authUser->can('Restore:TugasSayaResource');
    }

    public function forceDelete(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        return $authUser->can('ForceDelete:TugasSayaResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TugasSayaResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TugasSayaResource');
    }

    public function replicate(AuthUser $authUser, LaporanAktivitas $laporanAktivitas): bool
    {
        return $authUser->can('Replicate:TugasSayaResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TugasSayaResource');
    }

}