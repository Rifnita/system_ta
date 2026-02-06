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
        Schema::create('laporan_mingguan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyek_id')->constrained('proyek')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Informasi Periode
            $table->integer('minggu_ke');
            $table->date('tanggal_mulai');
            $table->date('tanggal_akhir');
            $table->year('tahun');
            
            // Progress & Achievement
            $table->decimal('persentase_penyelesaian', 5, 2)->default(0)->comment('dalam %');
            $table->decimal('target_mingguan', 5, 2)->nullable()->comment('target progress minggu ini dalam %');
            $table->decimal('realisasi_mingguan', 5, 2)->nullable()->comment('realisasi progress minggu ini dalam %');
            $table->text('area_dikerjakan')->nullable()->comment('area/zona yang dikerjakan');
            $table->text('pekerjaan_dilaksanakan')->nullable()->comment('deskripsi pekerjaan');
            
            // Resource Management
            $table->text('material_digunakan')->nullable();
            $table->integer('jumlah_pekerja')->nullable();
            
            // Quality Control
            $table->enum('status_kualitas', ['excellent', 'good', 'fair', 'poor'])->nullable();
            $table->text('temuan')->nullable()->comment('temuan baik/buruk');
            
            // Risk & Issues
            $table->text('kendala')->nullable();
            $table->text('solusi')->nullable();
            $table->text('dampak_timeline')->nullable();
            $table->enum('kondisi_cuaca', ['cerah', 'berawan', 'hujan_ringan', 'hujan_lebat'])->nullable();
            
            // Documentation
            $table->json('foto_progress')->nullable()->comment('array of photo paths, max 5 photos');
            
            // Planning
            $table->text('rencana_minggu_depan')->nullable();
            
            // Additional Info
            $table->text('catatan')->nullable();
            $table->timestamp('submitted_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('proyek_id');
            $table->index('user_id');
            $table->index(['tahun', 'minggu_ke']);
            $table->index('tanggal_mulai');
            $table->unique(['proyek_id', 'tahun', 'minggu_ke'], 'unique_proyek_periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_mingguan');
    }
};
