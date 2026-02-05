<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriLaporanAktivitasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kategori_laporan_aktivitas')->insertOrIgnore([
            ['nama' => 'Cek Rumah', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Survey Lokasi', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Meeting Client', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Pemasangan', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Perbaikan', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Administrasi', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Lainnya', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
