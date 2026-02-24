<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TransaksiKeuangan;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TransaksiKeuanganPolicy
{
    use HandlesAuthorization;

    private function canWithFallback(AuthUser $authUser, string ...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($authUser->can($permission)) {
                return true;
            }
        }

        return false;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $this->canWithFallback($authUser, 'ViewAny:TransaksiKeuangan', 'view_any_transaksi_keuangan');
    }

    public function view(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        if ($this->canWithFallback($authUser, 'View:TransaksiKeuangan', 'view_transaksi_keuangan')) {
            return true;
        }

        return (int) $transaksiKeuangan->user_id === (int) $authUser->id
            && $this->canWithFallback($authUser, 'create_transaksi_keuangan', 'Create:TransaksiKeuangan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $this->canWithFallback($authUser, 'Create:TransaksiKeuangan', 'create_transaksi_keuangan');
    }

    public function update(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        if ($this->canWithFallback($authUser, 'Update:TransaksiKeuangan', 'update_transaksi_keuangan')) {
            return true;
        }

        return (int) $transaksiKeuangan->user_id === (int) $authUser->id
            && $this->canWithFallback($authUser, 'create_transaksi_keuangan', 'Create:TransaksiKeuangan');
    }

    public function delete(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $this->canWithFallback($authUser, 'Delete:TransaksiKeuangan', 'delete_transaksi_keuangan');
    }

    public function restore(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $this->canWithFallback($authUser, 'Restore:TransaksiKeuangan', 'restore_transaksi_keuangan');
    }

    public function forceDelete(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $this->canWithFallback($authUser, 'ForceDelete:TransaksiKeuangan', 'force_delete_transaksi_keuangan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $this->canWithFallback($authUser, 'ForceDeleteAny:TransaksiKeuangan', 'force_delete_any_transaksi_keuangan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $this->canWithFallback($authUser, 'RestoreAny:TransaksiKeuangan', 'restore_any_transaksi_keuangan');
    }

    public function replicate(AuthUser $authUser, TransaksiKeuangan $transaksiKeuangan): bool
    {
        return $this->canWithFallback($authUser, 'Replicate:TransaksiKeuangan', 'replicate_transaksi_keuangan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $this->canWithFallback($authUser, 'Reorder:TransaksiKeuangan', 'reorder_transaksi_keuangan');
    }
}
