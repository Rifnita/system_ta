# Menu Laporan Aktivitas Harian

## Deskripsi
Menu untuk mencatat aktivitas harian pegawai properti yang bekerja di lapangan seperti cek rumah, survey lokasi, meeting client, dan aktivitas lainnya.

## Fitur yang Tersedia

### 1. **Input Aktivitas Harian**
- Tanggal aktivitas (maksimal hari ini)
- Kategori aktivitas:
  - Cek Rumah
  - Survey Lokasi
  - Meeting Client
  - Pemasangan
  - Perbaikan
  - Administrasi
  - Lainnya
- Judul aktivitas (ringkasan singkat)
- Deskripsi detail aktivitas
- Waktu mulai & selesai (otomatis menghitung durasi)
- Lokasi/alamat aktivitas
- Upload foto bukti (maksimal 5 foto, 2MB per foto)

### 2. **Tampilan Profesional**
- **Tab Filter Cepat**: Semua, Hari Ini, Minggu Ini, Bulan Ini
- **Filter Lanjutan**:
  - Filter berdasarkan pegawai (untuk admin/supervisor)
  - Filter berdasarkan kategori aktivitas
  - Filter berdasarkan rentang tanggal
- **Tabel Informatif**:
  - Menampilkan durasi aktivitas otomatis
  - Preview foto dalam gallery
  - Badge warna untuk kategori
  - Tooltip untuk teks panjang

### 3. **Detail View**
- Info lengkap aktivitas dengan layout profesional
- Gallery foto bukti aktivitas
- Informasi durasi aktivitas
- Metadata sistem (created_at, updated_at)

### 4. **Image Editor**
- Edit foto sebelum upload (crop, rotate, resize)
- Pilihan aspect ratio (16:9, 4:3, 1:1)
- Preview dan download foto

### 5. **Authorization & Security**
- User hanya bisa melihat dan mengedit laporan mereka sendiri
- Admin/Supervisor bisa melihat semua laporan
- Permission-based access control

## Struktur Database

### Tabel: `laporan_aktivitas`
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key ke users |
| tanggal_aktivitas | date | Tanggal aktivitas |
| judul | varchar(255) | Judul/ringkasan aktivitas |
| deskripsi | text | Deskripsi detail |
| waktu_mulai | time | Waktu mulai aktivitas |
| waktu_selesai | time | Waktu selesai aktivitas |
| lokasi | varchar(255) | Lokasi/alamat |
| kategori | enum | Kategori aktivitas |
| foto_bukti | json | Array path foto |
| created_at | timestamp | |
| updated_at | timestamp | |

## Permissions
- `view_laporan::aktivitas` - Melihat laporan sendiri
- `view_any_laporan::aktivitas` - Melihat semua laporan (admin)
- `create_laporan::aktivitas` - Membuat laporan
- `update_laporan::aktivitas` - Update laporan sendiri
- `delete_laporan::aktivitas` - Hapus laporan sendiri
- `delete_any_laporan::aktivitas` - Hapus semua laporan (admin)

## Role Permissions
- **User**: Bisa create, view, update, delete laporan mereka sendiri
- **Admin**: Bisa view semua laporan + create, update, delete sendiri
- **Super Admin**: Full akses

## Cara Menggunakan

### 1. Install & Setup
```bash
# Run migration
php artisan migrate

# Seed permissions (jika fresh install)
php artisan db:seed --class=RolePermissionSeeder

# Atau jika sudah ada data, tambahkan permission manual
php artisan shield:generate --all
```

### 2. Akses Menu
- Login ke Filament panel
- Navigasi: **Aktivitas** > **Laporan Aktivitas**

### 3. Tambah Laporan Aktivitas
1. Klik tombol "Tambah Laporan"
2. Isi form dengan lengkap
3. Upload foto bukti (opsional)
4. Klik "Simpan"

### 4. Lihat & Edit Laporan
- Gunakan tab filter untuk navigasi cepat
- Klik baris untuk melihat detail
- Klik icon edit untuk mengubah
- Klik icon delete untuk menghapus

## File yang Dibuat
1. **Migration**: `2026_02_06_000001_create_laporan_aktivitas_table.php`
2. **Model**: `app/Models/LaporanAktivitas.php`
3. **Policy**: `app/Policies/LaporanAktivitasPolicy.php`
4. **Resource**: `app/Filament/Resources/LaporanAktivitasResource.php`
5. **Pages**:
   - `ListLaporanAktivitas.php`
   - `CreateLaporanAktivitas.php`
   - `EditLaporanAktivitas.php`
   - `ViewLaporanAktivitas.php`

## Ide Pengembangan Lanjutan (Opsional)
1. **Dashboard Widget**: Statistik aktivitas bulanan
2. **Export Report**: Export ke Excel/PDF
3. **Calendar View**: Tampilan kalender aktivitas
4. **Reminder**: Notifikasi untuk input aktivitas harian
5. **GPS Location**: Auto capture lokasi saat input
6. **Timeline View**: Tampilan timeline aktivitas
7. **Performance Metrics**: Analisa produktivitas pegawai
