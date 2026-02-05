<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_laporan_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->timestamps();
        });

        // Ubah kolom kategori dari ENUM -> string supaya admin bisa menambah kategori via UI.
        // Gunakan SQL langsung agar tidak bergantung doctrine/dbal.
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `laporan_aktivitas` MODIFY `kategori` VARCHAR(255) NOT NULL DEFAULT 'Lainnya'");
        }

        // Seed default kategori (tanpa bergantung seeder global).
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

    public function down(): void
    {
        // Coba kembalikan ke ENUM untuk MySQL/MariaDB.
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `laporan_aktivitas` MODIFY `kategori` ENUM('Cek Rumah','Survey Lokasi','Meeting Client','Pemasangan','Perbaikan','Administrasi','Lainnya') NOT NULL DEFAULT 'Lainnya'");
        }

        Schema::dropIfExists('kategori_laporan_aktivitas');
    }
};
