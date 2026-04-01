<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TransaksiKeuangan;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransaksiKeuanganPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TransaksiKeuanganResource');
    }

    public function view(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $authUser->can('View:TransaksiKeuanganResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TransaksiKeuanganResource');
    }

    public function update(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $authUser->can('Update:TransaksiKeuanganResource');
    }

    public function delete(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $authUser->can('Delete:TransaksiKeuanganResource');
    }

    public function restore(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $authUser->can('Restore:TransaksiKeuanganResource');
    }

    public function forceDelete(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $authUser->can('ForceDelete:TransaksiKeuanganResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TransaksiKeuanganResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TransaksiKeuanganResource');
    }

    public function replicate(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $authUser->can('Replicate:TransaksiKeuanganResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TransaksiKeuanganResource');
    }

}