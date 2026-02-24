<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiKeuangan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'transaksi_keuangan';

    protected $fillable = [
        'user_id',
        'proyek_id',
        'kategori_transaksi_keuangan_id',
        'tanggal',
        'jenis',
        'nominal',
        'metode_pembayaran',
        'nomor_referensi',
        'deskripsi',
        'lampiran_bukti',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proyek(): BelongsTo
    {
        return $this->belongsTo(Proyek::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriTransaksiKeuangan::class, 'kategori_transaksi_keuangan_id');
    }
}
