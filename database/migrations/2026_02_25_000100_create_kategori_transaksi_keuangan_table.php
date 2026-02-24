<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_transaksi_keuangan', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->enum('jenis', ['pemasukan', 'pengeluaran']);
            $table->boolean('is_aktif')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['jenis', 'is_aktif']);
            $table->unique(['nama', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_transaksi_keuangan');
    }
};
