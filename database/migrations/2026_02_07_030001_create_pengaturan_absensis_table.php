<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengaturan_absensis', function (Blueprint $table) {
            $table->id();
            $table->time('jam_masuk_standar')->default('08:00:00');
            $table->time('jam_keluar_standar')->default('17:00:00');
            $table->integer('toleransi_keterlambatan')->default(15); // dalam menit
            $table->boolean('wajib_foto')->default(true);
            $table->boolean('wajib_lokasi')->default(true);
            $table->integer('radius_kantor')->default(100); // dalam meter
            $table->decimal('latitude_kantor', 10, 8)->nullable();
            $table->decimal('longitude_kantor', 11, 8)->nullable();
            $table->string('nama_lokasi')->default('Kantor Pusat');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
        
        // Insert default settings
        DB::table('pengaturan_absensis')->insert([
            'jam_masuk_standar' => '08:00:00',
            'jam_keluar_standar' => '17:00:00',
            'toleransi_keterlambatan' => 15,
            'wajib_foto' => true,
            'wajib_lokasi' => true,
            'radius_kantor' => 100,
            'nama_lokasi' => 'Kantor Pusat',
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_absensis');
    }
};
