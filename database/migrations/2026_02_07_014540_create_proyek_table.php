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
        Schema::create('proyek', function (Blueprint $table) {
            $table->id();
            $table->string('kode_proyek')->unique();
            $table->string('nama_proyek');
            $table->string('lokasi');
            $table->text('alamat_lengkap')->nullable();
            $table->enum('tipe_bangunan', [
                'rumah_tinggal', 
                'ruko', 
                'gedung', 
                'villa', 
                'apartemen', 
                'lainnya'
            ])->default('rumah_tinggal');
            $table->string('kontraktor')->nullable();
            $table->string('nama_pemilik')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('estimasi_selesai')->nullable();
            $table->decimal('nilai_kontrak', 15, 2)->nullable();
            $table->enum('status', [
                'perencanaan', 
                'dalam_pengerjaan', 
                'tertunda', 
                'selesai'
            ])->default('perencanaan');
            $table->text('deskripsi')->nullable();
            $table->decimal('luas_bangunan', 10, 2)->nullable()->comment('dalam m2');
            $table->decimal('luas_tanah', 10, 2)->nullable()->comment('dalam m2');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('status');
            $table->index('tanggal_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyek');
    }
};
