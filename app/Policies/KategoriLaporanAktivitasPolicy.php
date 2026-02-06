<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KategoriLaporanAktivitas;
use Illuminate\Auth\Access\HandlesAuthorization;

class KategoriLaporanAktivitasPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KategoriLaporanAktivitas');
    }

    public function view(AuthUser $authUser, KategoriLaporanAktivitas $kategoriLaporanAktivitas): bool
    {
        return $authUser->can('View:KategoriLaporanAktivitas');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KategoriLaporanAktivitas');
    }

    public function update(AuthUser $authUser, KategoriLaporanAktivitas $kategoriLaporanAktivitas): bool
    {
        return $authUser->can('Update:KategoriLaporanAktivitas');
    }

    public function delete(AuthUser $authUser, KategoriLaporanAktivitas $kategoriLaporanAktivitas): bool
    {
        return $authUser->can('Delete:KategoriLaporanAktivitas');
    }

    public function restore(AuthUser $authUser, KategoriLaporanAktivitas $kategoriLaporanAktivitas): bool
    {
        return $authUser->can('Restore:KategoriLaporanAktivitas');
    }

    public function forceDelete(AuthUser $authUser, KategoriLaporanAktivitas $kategoriLaporanAktivitas): bool
    {
        return $authUser->can('ForceDelete:KategoriLaporanAktivitas');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KategoriLaporanAktivitas');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KategoriLaporanAktivitas');
    }

    public function replicate(AuthUser $authUser, KategoriLaporanAktivitas $kategoriLaporanAktivitas): bool
    {
        return $authUser->can('Replicate:KategoriLaporanAktivitas');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KategoriLaporanAktivitas');
    }

}