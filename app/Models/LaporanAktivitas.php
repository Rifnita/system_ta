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
    ];

    protected $casts = [
        'tanggal_aktivitas' => 'date',
        // Kolom database bertipe TIME, jadi simpan sebagai string (mis. 08:30:00).
        'waktu_mulai' => 'string',
        'waktu_selesai' => 'string',
        'foto_bukti' => 'array',
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
}
