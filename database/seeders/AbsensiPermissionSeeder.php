<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AbsensiPermissionSeeder extends Seeder
{
    public function run(): void
    {
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

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Assign to super_admin
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
            $this->command->info('✓ Permissions assigned to super_admin');
        }

        // Assign to admin
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($permissions);
            $this->command->info('✓ Permissions assigned to admin');
        }

        $this->command->info('✓ Absensi permissions seeded successfully!');
    }
}
