<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriLaporanAktivitas extends Model
{
    use HasFactory;

    protected $table = 'kategori_laporan_aktivitas';

    protected $fillable = [
        'nama',
    ];

    /**
     * @return array<string, string>
     */
    public static function defaultOptions(): array
    {
        return [
            'Cek Rumah' => 'Cek Rumah',
            'Survey Lokasi' => 'Survey Lokasi',
            'Meeting Client' => 'Meeting Client',
            'Pemasangan' => 'Pemasangan',
            'Perbaikan' => 'Perbaikan',
            'Administrasi' => 'Administrasi',
            'Lainnya' => 'Lainnya',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        try {
            $options = self::query()
                ->orderBy('nama')
                ->pluck('nama', 'nama')
                ->all();

            return $options !== [] ? $options : self::defaultOptions();
        } catch (\Throwable) {
            // Table might not exist yet (fresh install / migrations not run)
            return self::defaultOptions();
        }
    }

    public static function colorFor(string $kategori): string
    {
        return match ($kategori) {
            'Cek Rumah' => 'primary',
            'Survey Lokasi' => 'success',
            'Meeting Client' => 'warning',
            'Pemasangan' => 'info',
            'Perbaikan' => 'danger',
            'Administrasi' => 'secondary',
            'Lainnya' => 'gray',
            default => 'gray',
        };
    }
}
