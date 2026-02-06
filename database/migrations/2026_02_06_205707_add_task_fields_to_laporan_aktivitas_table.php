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
            // Status tracking
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed', 'cancelled'])
                ->default('pending')
                ->after('kategori');
            
            $table->text('catatan_status')->nullable()->after('status');
            
            // Priority flag
            $table->boolean('is_priority')->default(false)->after('catatan_status');
            
            // Dokumen bukti untuk task completed
            $table->json('dokumen_bukti')->nullable()->after('foto_bukti');
            
            // Lokasi dengan koordinat untuk maps
            $table->text('alamat_lengkap')->nullable()->after('lokasi');
            $table->decimal('latitude', 10, 8)->nullable()->after('alamat_lengkap');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            
            // Target vs Actual time
            $table->datetime('target_start_time')->nullable()->after('longitude');
            $table->datetime('target_end_time')->nullable()->after('target_start_time');
            $table->datetime('actual_start_time')->nullable()->after('target_end_time');
            $table->datetime('actual_end_time')->nullable()->after('actual_start_time');
            
            // Index untuk performance
            $table->index('status');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_aktivitas', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'catatan_status',
                'is_priority',
                'dokumen_bukti',
                'alamat_lengkap',
                'latitude',
                'longitude',
                'target_start_time',
                'target_end_time',
                'actual_start_time',
                'actual_end_time',
            ]);
        });
    }
};
