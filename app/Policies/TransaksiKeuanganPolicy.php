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
        return $authUser->can('ViewAny:TransaksiKeuangan');
    }

    public function view(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $authUser->can('View:TransaksiKeuangan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TransaksiKeuangan');
    }

    public function update(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $authUser->can('Update:TransaksiKeuangan');
    }

    public function delete(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $authUser->can('Delete:TransaksiKeuangan');
    }

    public function restore(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $authUser->can('Restore:TransaksiKeuangan');
    }

    public function forceDelete(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $authUser->can('ForceDelete:TransaksiKeuangan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TransaksiKeuangan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TransaksiKeuangan');
    }

    public function replicate(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $authUser->can('Replicate:TransaksiKeuangan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TransaksiKeuangan');
    }

}