<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriTransaksiKeuangan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'kategori_transaksi_keuangan';

    protected $fillable = [
        'nama',
        'jenis',
        'is_aktif',
        'urutan',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
        'urutan' => 'integer',
    ];

    public function transaksiKeuangan(): HasMany
    {
        return $this->hasMany(TransaksiKeuangan::class, 'kategori_transaksi_keuangan_id');
    }
}
