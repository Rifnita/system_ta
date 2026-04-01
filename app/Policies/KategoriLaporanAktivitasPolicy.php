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
        return $authUser->can('ViewAny:KategoriTaskResource');
    }

    public function view(AuthUser $authUser, KategoriLaporanAktivitas $kategoriLaporanAktivitas): bool
    {
        return $authUser->can('View:KategoriTaskResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KategoriTaskResource');
    }

    public function update(AuthUser $authUser, KategoriLaporanAktivitas $kategoriLaporanAktivitas): bool
    {
        return $authUser->can('Update:KategoriTaskResource');
    }

    public function delete(AuthUser $authUser, KategoriLaporanAktivitas $kategoriLaporanAktivitas): bool
    {
        return $authUser->can('Delete:KategoriTaskResource');
    }

    public function restore(AuthUser $authUser, KategoriLaporanAktivitas $kategoriLaporanAktivitas): bool
    {
        return $authUser->can('Restore:KategoriTaskResource');
    }

    public function forceDelete(AuthUser $authUser, KategoriLaporanAktivitas $kategoriLaporanAktivitas): bool
    {
        return $authUser->can('ForceDelete:KategoriTaskResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KategoriTaskResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KategoriTaskResource');
    }

    public function replicate(AuthUser $authUser, KategoriLaporanAktivitas $kategoriLaporanAktivitas): bool
    {
        return $authUser->can('Replicate:KategoriTaskResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KategoriTaskResource');
    }

}