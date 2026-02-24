<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_keuangan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('proyek_id')->nullable()->constrained('proyek')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('kategori_transaksi_keuangan_id')
                ->constrained('kategori_transaksi_keuangan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('tanggal');
            $table->enum('jenis', ['pemasukan', 'pengeluaran']);
            $table->decimal('nominal', 15, 2);
            $table->enum('metode_pembayaran', ['kas', 'transfer_bank', 'e_wallet', 'kartu_debit', 'kartu_kredit', 'lainnya'])
                ->default('kas');
            $table->string('nomor_referensi')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('lampiran_bukti')->nullable();
            $table->enum('status', ['draft', 'tercatat'])->default('tercatat');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tanggal', 'jenis']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_keuangan');
    }
};
