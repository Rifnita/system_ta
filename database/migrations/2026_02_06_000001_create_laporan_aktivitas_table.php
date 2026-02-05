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
        Schema::create('laporan_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('tanggal_aktivitas');
            $table->string('judul');
            $table->text('deskripsi');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->string('lokasi')->nullable();
            $table->enum('kategori', ['Cek Rumah', 'Survey Lokasi', 'Meeting Client', 'Pemasangan', 'Perbaikan', 'Administrasi', 'Lainnya'])->default('Lainnya');
            $table->json('foto_bukti')->nullable(); // Store multiple photo paths
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'tanggal_aktivitas']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_aktivitas');
    }
};
