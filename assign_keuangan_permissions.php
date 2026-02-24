<?php

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$permissions = [
    'view_transaksi_keuangan',
    'view_any_transaksi_keuangan',
    'create_transaksi_keuangan',
    'update_transaksi_keuangan',
    'delete_transaksi_keuangan',
    'delete_any_transaksi_keuangan',
    'force_delete_transaksi_keuangan',
    'force_delete_any_transaksi_keuangan',
    'restore_transaksi_keuangan',
    'restore_any_transaksi_keuangan',
    'replicate_transaksi_keuangan',
    'reorder_transaksi_keuangan',
    'export_transaksi_keuangan',

    'view_kategori_transaksi_keuangan',
    'view_any_kategori_transaksi_keuangan',
    'create_kategori_transaksi_keuangan',
    'update_kategori_transaksi_keuangan',
    'delete_kategori_transaksi_keuangan',
    'delete_any_kategori_transaksi_keuangan',
    'force_delete_kategori_transaksi_keuangan',
    'force_delete_any_kategori_transaksi_keuangan',
    'restore_kategori_transaksi_keuangan',
    'restore_any_kategori_transaksi_keuangan',
    'replicate_kategori_transaksi_keuangan',
    'reorder_kategori_transaksi_keuangan',

    'View:TransaksiKeuangan',
    'ViewAny:TransaksiKeuangan',
    'Create:TransaksiKeuangan',
    'Update:TransaksiKeuangan',
    'Delete:TransaksiKeuangan',
    'DeleteAny:TransaksiKeuangan',
    'ForceDelete:TransaksiKeuangan',
    'ForceDeleteAny:TransaksiKeuangan',
    'Restore:TransaksiKeuangan',
    'RestoreAny:TransaksiKeuangan',
    'Replicate:TransaksiKeuangan',
    'Reorder:TransaksiKeuangan',
    'Export:TransaksiKeuangan',

    'View:KategoriTransaksiKeuangan',
    'ViewAny:KategoriTransaksiKeuangan',
    'Create:KategoriTransaksiKeuangan',
    'Update:KategoriTransaksiKeuangan',
    'Delete:KategoriTransaksiKeuangan',
    'DeleteAny:KategoriTransaksiKeuangan',
    'ForceDelete:KategoriTransaksiKeuangan',
    'ForceDeleteAny:KategoriTransaksiKeuangan',
    'Restore:KategoriTransaksiKeuangan',
    'RestoreAny:KategoriTransaksiKeuangan',
    'Replicate:KategoriTransaksiKeuangan',
    'Reorder:KategoriTransaksiKeuangan',
];

foreach ($permissions as $permission) {
    Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
}

foreach (['super_admin', 'admin'] as $roleName) {
    $role = Role::where('name', $roleName)->first();

    if ($role) {
        $role->givePermissionTo($permissions);
        echo "Permissions assigned to {$roleName}\n";
    }
}

echo "Done.\n";
