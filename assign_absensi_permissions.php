<?php

use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Create permissions if not exist
$permissions = [
    'view_absensi',
    'view_any_absensi',
    'create_absensi',
    'update_absensi',
    'delete_absensi',
    'delete_any_absensi',
    'force_delete_absensi',
    'force_delete_any_absensi',
    'restore_absensi',
    'restore_any_absensi',
    'replicate_absensi',
    'reorder_absensi',
    
    'view_pengaturan::absensi',
    'view_any_pengaturan::absensi',
    'create_pengaturan::absensi',
    'update_pengaturan::absensi',
    'delete_pengaturan::absensi',
    'delete_any_pengaturan::absensi',
    'force_delete_pengaturan::absensi',
    'force_delete_any_pengaturan::absensi',
    'restore_pengaturan::absensi',
    'restore_any_pengaturan::absensi',
    'replicate_pengaturan::absensi',
    'reorder_pengaturan::absensi',
];

foreach ($permissions as $permission) {
    Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
}

// Assign all permissions to super_admin role
$superAdmin = Role::where('name', 'super_admin')->first();
if ($superAdmin) {
    $superAdmin->givePermissionTo($permissions);
    echo "✓ All absensi permissions assigned to super_admin\n";
} else {
    echo "⚠ super_admin role not found\n";
}

// Also assign to admin role if exists
$admin = Role::where('name', 'admin')->first();
if ($admin) {
    $admin->givePermissionTo($permissions);
    echo "✓ All absensi permissions assigned to admin\n";
}

echo "\n✓ Done! Refresh browser to see the changes.\n";
