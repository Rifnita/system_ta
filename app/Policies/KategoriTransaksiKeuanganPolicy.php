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
        return $authUser->can('ViewAny:KategoriTransaksiKeuangan');
    }

    public function view(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $authUser->can('View:KategoriTransaksiKeuangan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KategoriTransaksiKeuangan');
    }

    public function update(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $authUser->can('Update:KategoriTransaksiKeuangan');
    }

    public function delete(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $authUser->can('Delete:KategoriTransaksiKeuangan');
    }

    public function restore(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $authUser->can('Restore:KategoriTransaksiKeuangan');
    }

    public function forceDelete(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $authUser->can('ForceDelete:KategoriTransaksiKeuangan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KategoriTransaksiKeuangan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KategoriTransaksiKeuangan');
    }

    public function replicate(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $authUser->can('Replicate:KategoriTransaksiKeuangan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KategoriTransaksiKeuangan');
    }

}