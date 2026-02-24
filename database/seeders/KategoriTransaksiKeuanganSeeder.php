<?php

namespace Database\Seeders;

use App\Models\KategoriTransaksiKeuangan;
use Illuminate\Database\Seeder;

class KategoriTransaksiKeuanganSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Penjualan', 'jenis' => 'pemasukan', 'urutan' => 1],
            ['nama' => 'Pembayaran Termin Proyek', 'jenis' => 'pemasukan', 'urutan' => 2],
            ['nama' => 'Pendapatan Lain-lain', 'jenis' => 'pemasukan', 'urutan' => 3],
            ['nama' => 'Pembelian Material', 'jenis' => 'pengeluaran', 'urutan' => 1],
            ['nama' => 'Upah Tenaga Kerja', 'jenis' => 'pengeluaran', 'urutan' => 2],
            ['nama' => 'Biaya Operasional', 'jenis' => 'pengeluaran', 'urutan' => 3],
            ['nama' => 'Transportasi', 'jenis' => 'pengeluaran', 'urutan' => 4],
            ['nama' => 'Lain-lain', 'jenis' => 'pengeluaran', 'urutan' => 5],
        ];

        foreach ($data as $item) {
            KategoriTransaksiKeuangan::firstOrCreate(
                [
                    'nama' => $item['nama'],
                    'jenis' => $item['jenis'],
                ],
                [
                    'urutan' => $item['urutan'],
                    'is_aktif' => true,
                ],
            );
        }
    }
}
