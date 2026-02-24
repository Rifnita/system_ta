<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RolePermissionSeeder::class,
            AbsensiPermissionSeeder::class,
            KeuanganPermissionSeeder::class,
            KategoriLaporanAktivitasSeeder::class,
            KategoriTransaksiKeuanganSeeder::class,
            TestUserSeeder::class,
        ]);
    }
}
