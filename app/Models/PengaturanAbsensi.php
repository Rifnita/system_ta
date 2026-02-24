<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanAbsensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'jam_masuk_standar',
        'jam_keluar_standar',
        'toleransi_keterlambatan',
        'wajib_foto',
        'wajib_lokasi',
        'radius_kantor',
        'latitude_kantor',
        'longitude_kantor',
        'nama_lokasi',
        'aktif',
    ];

    protected $casts = [
        'jam_masuk_standar' => 'datetime:H:i:s',
        'jam_keluar_standar' => 'datetime:H:i:s',
        'toleransi_keterlambatan' => 'integer',
        'wajib_foto' => 'boolean',
        'wajib_lokasi' => 'boolean',
        'radius_kantor' => 'integer',
        'latitude_kantor' => 'decimal:8',
        'longitude_kantor' => 'decimal:8',
        'aktif' => 'boolean',
    ];

    /**
     * Get pengaturan yang aktif (singleton pattern)
     */
    public static function getAktif(): ?self
    {
        return self::where('aktif', true)->first();
    }
}
