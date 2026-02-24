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
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_masuk');
            $table->time('jam_keluar')->nullable();
            $table->decimal('total_jam_kerja', 5, 2)->nullable();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'cuti', 'alpha', 'dinas_luar', 'lembur'])->default('hadir');
            $table->integer('keterlambatan_menit')->default(0);
            
            // GPS & Location tracking
            $table->decimal('latitude_masuk', 10, 8)->nullable();
            $table->decimal('longitude_masuk', 11, 8)->nullable();
            $table->decimal('latitude_keluar', 10, 8)->nullable();
            $table->decimal('longitude_keluar', 11, 8)->nullable();
            $table->decimal('akurasi_gps_masuk', 8, 2)->nullable(); // dalam meter
            $table->decimal('akurasi_gps_keluar', 8, 2)->nullable();
            $table->boolean('mock_location_detected_masuk')->default(false);
            $table->boolean('mock_location_detected_keluar')->default(false);
            
            // Foto tracking
            $table->string('foto_masuk')->nullable();
            $table->string('foto_keluar')->nullable();
            
            // Security tracking
            $table->string('ip_address_masuk')->nullable();
            $table->string('ip_address_keluar')->nullable();
            $table->string('user_agent_masuk', 500)->nullable();
            $table->string('user_agent_keluar', 500)->nullable();
            $table->string('device_id_masuk')->nullable(); // Browser fingerprint
            $table->string('device_id_keluar')->nullable();
            
            // Metadata
            $table->text('keterangan')->nullable();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status_persetujuan', ['pending', 'disetujui', 'ditolak'])->nullable();
            $table->timestamp('approved_at')->nullable();
            
            // Audit trail
            $table->timestamp('created_at_server')->useCurrent(); // Server timestamp tidak bisa diubah
            $table->timestamps();
            
            // Indexes
            $table->unique(['user_id', 'tanggal']); // Satu user hanya bisa satu absen per hari
            $table->index('tanggal');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
