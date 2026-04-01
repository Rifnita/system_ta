<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KategoriTransaksiKeuangan;
use Illuminate\Auth\Access\HandlesAuthorization;

class KategoriTransaksiKeuanganPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KategoriTransaksiKeuanganResource');
    }

    public function view(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $authUser->can('View:KategoriTransaksiKeuanganResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KategoriTransaksiKeuanganResource');
    }

    public function update(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $authUser->can('Update:KategoriTransaksiKeuanganResource');
    }

    public function delete(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $authUser->can('Delete:KategoriTransaksiKeuanganResource');
    }

    public function restore(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $authUser->can('Restore:KategoriTransaksiKeuanganResource');
    }

    public function forceDelete(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $authUser->can('ForceDelete:KategoriTransaksiKeuanganResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KategoriTransaksiKeuanganResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KategoriTransaksiKeuanganResource');
    }

    public function replicate(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $authUser->can('Replicate:KategoriTransaksiKeuanganResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KategoriTransaksiKeuanganResource');
    }

}