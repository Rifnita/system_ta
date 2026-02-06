<?php

namespace Database\Seeders;

use App\Models\Proyek;
use App\Models\LaporanMingguan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ProyekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample Proyek Data
        $proyekData = [
            [
                'kode_proyek' => 'PRJ-2026-001',
                'nama_proyek' => 'Pembangunan Rumah Tinggal 2 Lantai Bapak Ahmad',
                'lokasi' => 'Surabaya',
                'alamat_lengkap' => 'Jl. Raya Darmo No. 123, Surabaya',
                'tipe_bangunan' => 'rumah_tinggal',
                'kontraktor' => 'CV Mitra Bangun Jaya',
                'nama_pemilik' => 'Bapak Ahmad Subagyo',
                'tanggal_mulai' => Carbon::parse('2026-01-05'),
                'estimasi_selesai' => Carbon::parse('2026-07-05'),
                'nilai_kontrak' => 850000000,
                'status' => 'dalam_pengerjaan',
                'deskripsi' => 'Pembangunan rumah tinggal 2 lantai dengan luas bangunan 250m2',
                'luas_bangunan' => 250,
                'luas_tanah' => 300,
            ],
            [
                'kode_proyek' => 'PRJ-2026-002',
                'nama_proyek' => 'Renovasi Ruko 3 Lantai',
                'lokasi' => 'Malang',
                'alamat_lengkap' => 'Jl. Veteran No. 45, Malang',
                'tipe_bangunan' => 'ruko',
                'kontraktor' => 'PT Karya Mandiri',
                'nama_pemilik' => 'Ibu Siti Nurjanah',
                'tanggal_mulai' => Carbon::parse('2026-01-15'),
                'estimasi_selesai' => Carbon::parse('2026-05-15'),
                'nilai_kontrak' => 450000000,
                'status' => 'dalam_pengerjaan',
                'deskripsi' => 'Renovasi total ruko 3 lantai untuk showroom dan kantor',
                'luas_bangunan' => 180,
                'luas_tanah' => 120,
            ],
            [
                'kode_proyek' => 'PRJ-2026-003',
                'nama_proyek' => 'Villa Puncak Resort',
                'lokasi' => 'Batu',
                'alamat_lengkap' => 'Jl. Raya Selecta KM 5, Batu',
                'tipe_bangunan' => 'villa',
                'kontraktor' => 'CV Indah Karya',
                'nama_pemilik' => 'PT Puncak Indah Resort',
                'tanggal_mulai' => Carbon::parse('2025-12-01'),
                'estimasi_selesai' => Carbon::parse('2026-08-01'),
                'nilai_kontrak' => 1500000000,
                'status' => 'dalam_pengerjaan',
                'deskripsi' => 'Pembangunan villa mewah dengan konsep modern minimalis',
                'luas_bangunan' => 400,
                'luas_tanah' => 600,
            ],
        ];

        foreach ($proyekData as $data) {
            Proyek::create($data);
        }

        // Create sample laporan mingguan for proyek
        $proyek = Proyek::first();
        $user = User::first();

        if ($proyek && $user) {
            for ($mingguKe = 1; $mingguKe <= 5; $mingguKe++) {
                $tanggalMulai = Carbon::parse('2026-01-05')->addWeeks($mingguKe - 1)->startOfWeek();
                $tanggalAkhir = $tanggalMulai->copy()->endOfWeek();

                LaporanMingguan::create([
                    'proyek_id' => $proyek->id,
                    'user_id' => $user->id,
                    'minggu_ke' => $mingguKe,
                    'tanggal_mulai' => $tanggalMulai,
                    'tanggal_akhir' => $tanggalAkhir,
                    'tahun' => 2026,
                    'persentase_penyelesaian' => $mingguKe * 5,
                    'target_mingguan' => 5,
                    'realisasi_mingguan' => rand(4, 6),
                    'area_dikerjakan' => 'Minggu ' . $mingguKe . ': ' . match($mingguKe) {
                        1 => 'Pembersihan lahan dan persiapan pondasi',
                        2 => 'Pekerjaan pondasi dan sloof',
                        3 => 'Struktur kolom lantai 1',
                        4 => 'Dinding lantai 1 dan struktur lantai 2',
                        5 => 'Kolom dan dinding lantai 2',
                        default => 'Pekerjaan lanjutan',
                    },
                    'pekerjaan_dilaksanakan' => 'Detail pekerjaan minggu ke-' . $mingguKe,
                    'material_digunakan' => 'Semen 50 sak, Besi 1 ton, Batu bata 5000 pcs',
                    'jumlah_pekerja' => rand(15, 25),
                    'status_kualitas' => ['excellent', 'good', 'good', 'fair', 'good'][rand(0, 4)],
                    'temuan' => 'Progress sesuai rencana, kualitas pekerjaan baik',
                    'kendala' => $mingguKe == 4 ? 'Hujan selama 2 hari menghambat pekerjaan' : 'Tidak ada kendala berarti',
                    'solusi' => $mingguKe == 4 ? 'Menambah jam kerja untuk mengejar target' : 'N/A',
                    'dampak_timeline' => $mingguKe == 4 ? 'Keterlambatan 1 hari' : 'Tidak ada',
                    'kondisi_cuaca' => ['cerah', 'berawan', 'cerah', 'hujan_ringan', 'cerah'][$mingguKe - 1],
                    'foto_progress' => null,
                    'rencana_minggu_depan' => 'Melanjutkan ke tahap berikutnya sesuai schedule',
                    'catatan' => 'Laporan minggu ke-' . $mingguKe,
                    'submitted_at' => $tanggalAkhir->copy()->addDays(1)->setTime(14, 0),
                ]);
            }
        }
    }
}
