<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PengaturanAbsensi;

class PengaturanAbsensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama jika ada (karena harusnya hanya 1 record)
        PengaturanAbsensi::truncate();
        
        // Buat pengaturan default
        PengaturanAbsensi::create([
            'jam_masuk_standar' => '08:00:00',
            'jam_keluar_standar' => '17:00:00',
            'latitude_kantor' => -5.381937, // Contoh: Koordinat dari screenshot user
            'longitude_kantor' => 105.194854,
            'radius_kantor' => 200, // 200 meter radius
            'toleransi_keterlambatan' => 15, // 15 menit
            'wajib_foto' => false, // Set false dulu untuk testing
            'wajib_lokasi' => false, // Set false dulu untuk testing
        ]);
        
        $this->command->info('✓ Pengaturan Absensi default berhasil dibuat');
        $this->command->warn('⚠ Wajib Foto dan Wajib Lokasi diset FALSE untuk kemudahan testing');
        $this->command->info('  Koordinat: -5.381937, 105.194854');
        $this->command->info('  Radius: 200 meter');
    }
}

