<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'total_jam_kerja',
        'status',
        'keterlambatan_menit',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_keluar',
        'longitude_keluar',
        'akurasi_gps_masuk',
        'akurasi_gps_keluar',
        'mock_location_detected_masuk',
        'mock_location_detected_keluar',
        'foto_masuk',
        'foto_keluar',
        'ip_address_masuk',
        'ip_address_keluar',
        'user_agent_masuk',
        'user_agent_keluar',
        'device_id_masuk',
        'device_id_keluar',
        'keterangan',
        'disetujui_oleh',
        'status_persetujuan',
        'approved_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_jam_kerja' => 'decimal:2',
        'keterlambatan_menit' => 'integer',
        'latitude_masuk' => 'decimal:8',
        'longitude_masuk' => 'decimal:8',
        'latitude_keluar' => 'decimal:8',
        'longitude_keluar' => 'decimal:8',
        'akurasi_gps_masuk' => 'decimal:2',
        'akurasi_gps_keluar' => 'decimal:2',
        'mock_location_detected_masuk' => 'boolean',
        'mock_location_detected_keluar' => 'boolean',
        'approved_at' => 'datetime',
        'created_at_server' => 'datetime',
    ];

    /**
     * Relasi ke User yang melakukan absensi
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke User yang menyetujui (untuk izin/cuti)
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /**
     * Hitung keterlambatan otomatis
     */
    public function hitungKeterlambatan(): int
    {
        $pengaturan = PengaturanAbsensi::getAktif();

        if (! $pengaturan) {
            return 0;
        }
        
        // Ekstrak hanya bagian waktu (H:i:s) dari jam_masuk
        $jamMasukValue = $this->extractTimeOnly($this->jam_masuk);
        
        $tanggalValue = $this->tanggal instanceof \Carbon\Carbon 
            ? $this->tanggal->format('Y-m-d') 
            : $this->tanggal;
        
        $jamMasukStandarValue = $this->extractTimeOnly($pengaturan->jam_masuk_standar);
        
        $jamMasukStandar = Carbon::parse($tanggalValue . ' ' . $jamMasukStandarValue);
        $jamMasukAktual = Carbon::parse($tanggalValue . ' ' . $jamMasukValue);
        
        $selisih = $jamMasukStandar->diffInMinutes($jamMasukAktual, false);
        $keterlambatan = $selisih > 0 ? $selisih : 0;

        if ($keterlambatan <= (int) $pengaturan->toleransi_keterlambatan) {
            return 0;
        }

        return $keterlambatan;
    }
    
    /**
     * Ekstrak hanya bagian waktu (H:i:s) dari value yang bisa berupa datetime string, Carbon, atau time string
     */
    private function extractTimeOnly($value): string
    {
        if ($value instanceof \Carbon\Carbon) {
            return $value->format('H:i:s');
        }
        
        if (is_string($value)) {
            // Jika format adalah "Y-m-d H:i:s" atau "Y-m-d H:i:s..."
            if (preg_match('/^\d{4}-\d{2}-\d{2}\s+(\d{2}:\d{2}:\d{2})/', $value, $matches)) {
                return $matches[1];
            }
            // Jika sudah format waktu saja "H:i:s"
            if (preg_match('/^(\d{2}:\d{2}:\d{2})$/', $value, $matches)) {
                return $matches[1];
            }
            // Jika format "H:i"
            if (preg_match('/^(\d{2}:\d{2})$/', $value, $matches)) {
                return $matches[1] . ':00';
            }
        }
        
        // Fallback: coba parse dengan Carbon dan ambil waktu saja
        try {
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            return '00:00:00';
        }
    }

    /**
     * Hitung total jam kerja otomatis
     */
    public function hitungTotalJamKerja(): ?float
    {
        if (!$this->jam_keluar) {
            return null;
        }

        // Ekstrak hanya bagian waktu (H:i:s)
        $jamMasukValue = $this->extractTimeOnly($this->jam_masuk);
        $jamKeluarValue = $this->extractTimeOnly($this->jam_keluar);
        
        $tanggalValue = $this->tanggal instanceof \Carbon\Carbon 
            ? $this->tanggal->format('Y-m-d') 
            : $this->tanggal;

        $masuk = Carbon::parse($tanggalValue . ' ' . $jamMasukValue);
        $keluar = Carbon::parse($tanggalValue . ' ' . $jamKeluarValue);
        
        return round($keluar->diffInMinutes($masuk) / 60, 2);
    }

    /**
     * Validasi jarak dari kantor
     */
    public function validasiJarakDariKantor(float $lat, float $lon): array
    {
        $pengaturan = PengaturanAbsensi::getAktif();

        if (! $pengaturan) {
            return ['valid' => true, 'jarak' => 0, 'message' => 'Pengaturan absensi belum aktif'];
        }
        
        if (!$pengaturan->latitude_kantor || !$pengaturan->longitude_kantor) {
            return ['valid' => true, 'jarak' => 0, 'message' => 'Lokasi kantor belum diatur'];
        }

        $jarak = $this->hitungJarak(
            (float) $pengaturan->latitude_kantor,
            (float) $pengaturan->longitude_kantor,
            $lat,
            $lon
        );

        $valid = $jarak <= $pengaturan->radius_kantor;

        return [
            'valid' => $valid,
            'jarak' => round($jarak, 2),
            'radius' => $pengaturan->radius_kantor,
            'message' => $valid 
                ? 'Lokasi valid' 
                : "Anda berada {$jarak}m dari kantor (max {$pengaturan->radius_kantor}m)"
        ];
    }

    /**
     * Hitung jarak antara 2 koordinat (Haversine formula)
     */
    private function hitungJarak(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // dalam meter

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $latDiff = $lat2 - $lat1;
        $lonDiff = $lon2 - $lon1;

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos($lat1) * cos($lat2) *
             sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Cek apakah user sudah absen hari ini
     */
    public static function sudahAbsenHariIni(int $userId): bool
    {
        return self::query()->hariIni($userId)->exists();
    }

    public function evaluasiRisikoGps(
        ?float $akurasi,
        bool $mockLocationDetected,
        ?float $latitude = null,
        ?float $longitude = null
    ): array {
        $score = 0;
        $alasan = [];

        if ($mockLocationDetected) {
            $score += 50;
            $alasan[] = 'Mock location terdeteksi dari perangkat';
        }

        if ($akurasi === null) {
            $score += 20;
            $alasan[] = 'Akurasi GPS tidak tersedia';
        } elseif ($akurasi < 5) {
            $score += 35;
            $alasan[] = 'Akurasi terlalu sempurna (indikasi spoofing)';
        } elseif ($akurasi > 250) {
            $score += 25;
            $alasan[] = 'Akurasi GPS sangat rendah';
        } elseif ($akurasi > 100) {
            $score += 10;
            $alasan[] = 'Akurasi GPS rendah';
        }

        if ($latitude === null || $longitude === null) {
            $score += 30;
            $alasan[] = 'Koordinat tidak lengkap';
        }

        $level = match (true) {
            $score >= 70 => 'tinggi',
            $score >= 40 => 'sedang',
            default => 'rendah',
        };

        return [
            'score' => $score,
            'level' => $level,
            'blocked' => $score >= 70,
            'alasan' => $alasan,
        ];
    }

    public static function absensiHariIni(int $userId): ?self
    {
        return self::query()
            ->hariIni($userId)
            ->first();
    }

    public function sudahAbsenKeluar(): bool
    {
        return filled($this->jam_keluar);
    }

    public function canCheckoutBy(User $user): bool
    {
        return (int) $this->user_id === (int) $user->id
            && $this->tanggal?->isToday()
            && ! $this->sudahAbsenKeluar();
    }

    public function scopeHariIni(Builder $query, ?int $userId = null): Builder
    {
        return $query
            ->when($userId, fn (Builder $q) => $q->where('user_id', $userId))
            ->whereDate('tanggal', today());
    }

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-calculate sebelum save
        static::saving(function ($absensi) {
            // Hitung keterlambatan
            if ($absensi->jam_masuk && !$absensi->keterlambatan_menit) {
                $absensi->keterlambatan_menit = $absensi->hitungKeterlambatan();
            }

            // Hitung total jam kerja
            if ($absensi->jam_keluar && !$absensi->total_jam_kerja) {
                $absensi->total_jam_kerja = $absensi->hitungTotalJamKerja();
            }
        });
    }
}
