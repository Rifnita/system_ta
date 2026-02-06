<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaporanMingguan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'laporan_mingguan';

    protected $fillable = [
        'proyek_id',
        'user_id',
        'minggu_ke',
        'tanggal_mulai',
        'tanggal_akhir',
        'tahun',
        'persentase_penyelesaian',
        'target_mingguan',
        'realisasi_mingguan',
        'area_dikerjakan',
        'pekerjaan_dilaksanakan',
        'material_digunakan',
        'jumlah_pekerja',
        'status_kualitas',
        'temuan',
        'kendala',
        'solusi',
        'dampak_timeline',
        'kondisi_cuaca',
        'foto_progress',
        'rencana_minggu_depan',
        'catatan',
        'submitted_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_akhir' => 'date',
        'foto_progress' => 'array',
        'persentase_penyelesaian' => 'decimal:2',
        'jumlah_pekerja' => 'integer',
        'submitted_at' => 'datetime',
    ];

    // Status kualitas constants
    const STATUS_EXCELLENT = 'excellent';
    const STATUS_GOOD = 'good';
    const STATUS_FAIR = 'fair';
    const STATUS_POOR = 'poor';

    // Kondisi cuaca constants
    const CUACA_CERAH = 'cerah';
    const CUACA_BERAWAN = 'berawan';
    const CUACA_HUJAN_RINGAN = 'hujan_ringan';
    const CUACA_HUJAN_LEBAT = 'hujan_lebat';

    /**
     * Get proyek yang dilaporkan
     */
    public function proyek()
    {
        return $this->belongsTo(Proyek::class);
    }

    /**
     * Get user yang membuat laporan
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get status kualitas label
     */
    public function getStatusKualitasLabelAttribute(): string
    {
        return match($this->status_kualitas) {
            self::STATUS_EXCELLENT => 'Sangat Baik',
            self::STATUS_GOOD => 'Baik',
            self::STATUS_FAIR => 'Cukup',
            self::STATUS_POOR => 'Buruk',
            default => 'Tidak Dinilai',
        };
    }

    /**
     * Get kondisi cuaca label
     */
    public function getKondisiCuacaLabelAttribute(): string
    {
        return match($this->kondisi_cuaca) {
            self::CUACA_CERAH => 'Cerah',
            self::CUACA_BERAWAN => 'Berawan',
            self::CUACA_HUJAN_RINGAN => 'Hujan Ringan',
            self::CUACA_HUJAN_LEBAT => 'Hujan Lebat',
            default => '-',
        };
    }

    /**
     * Get periode label (format: Minggu 1 - Jan 2026)
     */
    public function getPeriodeLabelAttribute(): string
    {
        $bulan = $this->tanggal_mulai->locale('id')->format('M Y');
        return "Minggu {$this->minggu_ke} - {$bulan}";
    }

    /**
     * Get selisih target vs realisasi
     */
    public function getSelisihTargetRealisasiAttribute(): ?float
    {
        if (is_null($this->target_mingguan) || is_null($this->realisasi_mingguan)) {
            return null;
        }
        
        return $this->realisasi_mingguan - $this->target_mingguan;
    }

    /**
     * Check apakah target tercapai
     */
    public function isTargetTercapai(): bool
    {
        if (is_null($this->target_mingguan) || is_null($this->realisasi_mingguan)) {
            return false;
        }
        
        return $this->realisasi_mingguan >= $this->target_mingguan;
    }

    /**
     * Get jumlah foto
     */
    public function getJumlahFotoAttribute(): int
    {
        return is_array($this->foto_progress) ? count($this->foto_progress) : 0;
    }

    /**
     * Scope untuk filter by proyek
     */
    public function scopeByProyek($query, $proyekId)
    {
        return $query->where('proyek_id', $proyekId);
    }

    /**
     * Scope untuk filter by periode
     */
    public function scopeByPeriode($query, $tahun, $mingguKe = null)
    {
        $query->where('tahun', $tahun);
        
        if ($mingguKe) {
            $query->where('minggu_ke', $mingguKe);
        }
        
        return $query;
    }

    /**
     * Scope untuk filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
