<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proyek extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'proyek';

    protected $fillable = [
        'kode_proyek',
        'nama_proyek',
        'lokasi',
        'alamat_lengkap',
        'tipe_bangunan',
        'kontraktor',
        'nama_pemilik',
        'tanggal_mulai',
        'estimasi_selesai',
        'nilai_kontrak',
        'status',
        'deskripsi',
        'luas_bangunan',
        'luas_tanah',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'estimasi_selesai' => 'date',
        'nilai_kontrak' => 'decimal:2',
        'luas_bangunan' => 'decimal:2',
        'luas_tanah' => 'decimal:2',
    ];

    // Status constants
    const STATUS_PERENCANAAN = 'perencanaan';
    const STATUS_DALAM_PENGERJAAN = 'dalam_pengerjaan';
    const STATUS_TERTUNDA = 'tertunda';
    const STATUS_SELESAI = 'selesai';

    // Tipe bangunan constants
    const TIPE_RUMAH_TINGGAL = 'rumah_tinggal';
    const TIPE_RUKO = 'ruko';
    const TIPE_GEDUNG = 'gedung';
    const TIPE_VILLA = 'villa';
    const TIPE_APARTEMEN = 'apartemen';
    const TIPE_LAINNYA = 'lainnya';

    /**
     * Get laporan mingguan untuk proyek ini
     */
    public function laporanMingguan()
    {
        return $this->hasMany(LaporanMingguan::class);
    }

    /**
     * Get status label dengan badge color
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PERENCANAAN => 'Perencanaan',
            self::STATUS_DALAM_PENGERJAAN => 'Dalam Pengerjaan',
            self::STATUS_TERTUNDA => 'Tertunda',
            self::STATUS_SELESAI => 'Selesai',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Get tipe bangunan label
     */
    public function getTipeBangunanLabelAttribute(): string
    {
        return match($this->tipe_bangunan) {
            self::TIPE_RUMAH_TINGGAL => 'Rumah Tinggal',
            self::TIPE_RUKO => 'Ruko',
            self::TIPE_GEDUNG => 'Gedung',
            self::TIPE_VILLA => 'Villa',
            self::TIPE_APARTEMEN => 'Apartemen',
            self::TIPE_LAINNYA => 'Lainnya',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Scope untuk filter proyek aktif
     */
    public function scopeAktif($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PERENCANAAN,
            self::STATUS_DALAM_PENGERJAAN
        ]);
    }

    /**
     * Get durasi proyek dalam hari
     */
    public function getDurasiHariAttribute(): ?int
    {
        if (!$this->tanggal_mulai || !$this->estimasi_selesai) {
            return null;
        }
        
        return $this->tanggal_mulai->diffInDays($this->estimasi_selesai);
    }

    /**
     * Get progress keseluruhan dari laporan mingguan terakhir
     */
    public function getProgressTerakhirAttribute(): ?float
    {
        $laporanTerakhir = $this->laporanMingguan()
            ->latest('tanggal_akhir')
            ->first();
            
        return $laporanTerakhir?->persentase_penyelesaian;
    }
}
