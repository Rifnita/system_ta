<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\KategoriTransaksiKeuangan;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class KategoriTransaksiKeuanganPolicy
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
        return $this->canWithFallback($authUser, 'ViewAny:KategoriTransaksiKeuangan', 'view_any_kategori_transaksi_keuangan');
    }

    public function view(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $this->canWithFallback($authUser, 'View:KategoriTransaksiKeuangan', 'view_kategori_transaksi_keuangan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $this->canWithFallback($authUser, 'Create:KategoriTransaksiKeuangan', 'create_kategori_transaksi_keuangan');
    }

    public function update(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $this->canWithFallback($authUser, 'Update:KategoriTransaksiKeuangan', 'update_kategori_transaksi_keuangan');
    }

    public function delete(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $this->canWithFallback($authUser, 'Delete:KategoriTransaksiKeuangan', 'delete_kategori_transaksi_keuangan');
    }

    public function restore(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $this->canWithFallback($authUser, 'Restore:KategoriTransaksiKeuangan', 'restore_kategori_transaksi_keuangan');
    }

    public function forceDelete(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $this->canWithFallback($authUser, 'ForceDelete:KategoriTransaksiKeuangan', 'force_delete_kategori_transaksi_keuangan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $this->canWithFallback($authUser, 'ForceDeleteAny:KategoriTransaksiKeuangan', 'force_delete_any_kategori_transaksi_keuangan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $this->canWithFallback($authUser, 'RestoreAny:KategoriTransaksiKeuangan', 'restore_any_kategori_transaksi_keuangan');
    }

    public function replicate(AuthUser $authUser, KategoriTransaksiKeuangan $kategoriTransaksiKeuangan): bool
    {
        return $this->canWithFallback($authUser, 'Replicate:KategoriTransaksiKeuangan', 'replicate_kategori_transaksi_keuangan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $this->canWithFallback($authUser, 'Reorder:KategoriTransaksiKeuangan', 'reorder_kategori_transaksi_keuangan');
    }
}
