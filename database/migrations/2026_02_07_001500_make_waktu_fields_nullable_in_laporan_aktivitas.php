<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('laporan_aktivitas', function (Blueprint $table) {
            // Ubah waktu_mulai dan waktu_selesai jadi nullable
            // karena sekarang kita pakai target_start_time, target_end_time, etc
            $table->time('waktu_mulai')->nullable()->change();
            $table->time('waktu_selesai')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_aktivitas', function (Blueprint $table) {
            $table->time('waktu_mulai')->nullable(false)->change();
            $table->time('waktu_selesai')->nullable(false)->change();
        });
    }
};
