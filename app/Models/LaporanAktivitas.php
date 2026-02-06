<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class LaporanAktivitas extends Model
{
    use HasFactory;

    protected $table = 'laporan_aktivitas';

    protected $fillable = [
        'user_id',
        'tanggal_aktivitas',
        'judul',
        'deskripsi',
        'waktu_mulai',
        'waktu_selesai',
        'lokasi',
        'kategori',
        'foto_bukti',
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
    ];

    protected $casts = [
        'tanggal_aktivitas' => 'date',
        // Kolom database bertipe TIME, jadi simpan sebagai string (mis. 08:30:00).
        'waktu_mulai' => 'string',
        'waktu_selesai' => 'string',
        'foto_bukti' => 'array',
        'dokumen_bukti' => 'array',
        'is_priority' => 'boolean',
        'target_start_time' => 'datetime',
        'target_end_time' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the user that owns the laporan aktivitas.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the duration in hours.
     */
    public function getDurasiAttribute(): string
    {
        if (!$this->waktu_mulai || !$this->waktu_selesai) {
            return '-';
        }

        $mulai = $this->waktu_mulai;
        $selesai = $this->waktu_selesai;

        if ($mulai instanceof \DateTimeInterface) {
            $mulaiCarbon = Carbon::instance($mulai);
        } else {
            $mulaiCarbon = Carbon::hasFormat((string) $mulai, 'H:i:s')
                ? Carbon::createFromFormat('H:i:s', (string) $mulai)
                : Carbon::createFromFormat('H:i', (string) $mulai);
        }

        if ($selesai instanceof \DateTimeInterface) {
            $selesaiCarbon = Carbon::instance($selesai);
        } else {
            $selesaiCarbon = Carbon::hasFormat((string) $selesai, 'H:i:s')
                ? Carbon::createFromFormat('H:i:s', (string) $selesai)
                : Carbon::createFromFormat('H:i', (string) $selesai);
        }

        $durasiMenit = $mulaiCarbon->diffInMinutes($selesaiCarbon);

        $jam = intdiv($durasiMenit, 60);
        $menit = $durasiMenit % 60;

        return sprintf('%d jam %d menit', $jam, $menit);
    }

    /**
     * Get actual duration in hours (from actual_start_time to actual_end_time).
     */
    public function getActualDurasiAttribute(): string
    {
        if (!$this->actual_start_time || !$this->actual_end_time) {
            return '-';
        }

        $durasiMenit = $this->actual_start_time->diffInMinutes($this->actual_end_time);
        $jam = intdiv($durasiMenit, 60);
        $menit = $durasiMenit % 60;

        return sprintf('%d jam %d menit', $jam, $menit);
    }

    /**
     * Get status label for display.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Belum Dimulai',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
            'failed' => 'Gagal',
            'cancelled' => 'Dibatalkan',
            default => '-',
        };
    }

    /**
     * Get status color for badges.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Check if task is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if task requires dokumen bukti.
     */
    public function needsDokumenBukti(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Scope: Filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter priority tasks.
     */
    public function scopePriority($query)
    {
        return $query->where('is_priority', true);
    }

    /**
     * Scope: Filter today's tasks.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal_aktivitas', today());
    }
}
