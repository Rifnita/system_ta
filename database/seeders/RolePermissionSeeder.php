<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User permissions
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
            'delete_user',
            'delete_any_user',
            
            // Laporan Aktivitas permissions
            'view_laporan::aktivitas',
            'view_any_laporan::aktivitas',
            'create_laporan::aktivitas',
            'update_laporan::aktivitas',
            'delete_laporan::aktivitas',
            'delete_any_laporan::aktivitas',
            'force_delete_laporan::aktivitas',
            'force_delete_any_laporan::aktivitas',
            'restore_laporan::aktivitas',
            'restore_any_laporan::aktivitas',
            'replicate_laporan::aktivitas',
            'reorder_laporan::aktivitas',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
            'view_laporan::aktivitas',
            'view_any_laporan::aktivitas',
            'create_laporan::aktivitas',
            'update_laporan::aktivitas',
            'delete_laporan::aktivitas',
        ]);

        $user = Role::create(['name' => 'user']);
        $user->givePermissionTo([
            'view_user',
            'view_laporan::aktivitas',
            'create_laporan::aktivitas',
            'update_laporan::aktivitas',
            'delete_laporan::aktivitas',
        ]);
    }
}
