# System TA

Aplikasi manajemen tugas, laporan aktivitas, absensi, dan transaksi keuangan berbasis Laravel 12 + Filament 4.

## 1. Prasyarat

Pastikan tools berikut sudah terpasang:

- PHP `>= 8.2`
- Composer `>= 2.x`
- Node.js `>= 20` dan npm
- MySQL/MariaDB (disarankan MySQL 8+)
- Git

Ekstensi PHP yang umumnya wajib untuk proyek ini:

- `bcmath`
- `ctype`
- `fileinfo`
- `json`
- `mbstring`
- `openssl`
- `pdo`
- `pdo_mysql`
- `tokenizer`
- `xml`
- `zip`
- `gd`

Jika pakai Laragon, aktifkan versi PHP 8.2+ dan MySQL dari menu Laragon.

## 2. Clone Project

```bash
git clone <url-repository> system_ta
cd system_ta
```

## 3. Install Dependency Backend (PHP)

```bash
composer install
```

## 4. Install Dependency Frontend (Node)

```bash
npm install
```

## 5. Buat dan Atur File Environment

Copy file contoh environment:

```bash
cp .env.example .env
```

Jika di Windows PowerShell dan `cp` tidak tersedia, pakai:

```powershell
Copy-Item .env.example .env
```

Lalu sesuaikan `.env` minimal bagian database:

```env
APP_NAME="System TA"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=system_ta
DB_USERNAME=root
DB_PASSWORD=
```

## 6. Buat Database

Buat database kosong bernama `system_ta` di MySQL.

Contoh via MySQL CLI:

```sql
CREATE DATABASE system_ta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 7. Generate App Key

```bash
php artisan key:generate
```

## 8. Migrasi dan Seeder

Jalankan migrasi:

```bash
php artisan migrate
```

Lalu isi data awal:

```bash
php artisan db:seed
```

Seeder akan membuat akun admin default:

- Email: `admin@test.com`
- Password: `password`

## 9. Link Storage (Jika Butuh File Upload)

```bash
php artisan storage:link
```

## 10. Build Asset Frontend

Untuk development (watch mode):

```bash
npm run dev
```

Untuk build production:

```bash
npm run build
```

## 11. Jalankan Aplikasi

Buka terminal baru lalu jalankan:

```bash
php artisan serve
```

Akses aplikasi di:

- Home: `http://127.0.0.1:8000`
- Admin panel: `http://127.0.0.1:8000/admin/login`

## 12. (Opsional) Jalankan Queue Worker

Karena default `QUEUE_CONNECTION=database`, jalankan worker jika fitur tertentu butuh queue:

```bash
php artisan queue:work
```

## 13. Opsi Setup Cepat

Proyek ini punya script Composer `setup`:

```bash
composer run setup
```

Script ini menjalankan:

- `composer install`
- copy `.env` jika belum ada
- `php artisan key:generate`
- `php artisan migrate --force`
- `npm install`
- `npm run build`

Catatan: script `setup` tidak menjalankan `db:seed`, jadi tetap jalankan:

```bash
php artisan db:seed
```

## 14. Troubleshooting Singkat

- Error koneksi database
  - Pastikan service MySQL aktif.
  - Cek `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` di `.env`.

- Asset/CSS tidak muncul
  - Jalankan `npm run dev` (local) atau `npm run build` (production).

- Login gagal dengan akun default
  - Jalankan ulang `php artisan db:seed`.
  - Pastikan tabel `users`, `roles`, `permissions` sudah terisi.

- Perubahan `.env` tidak terbaca
  - Jalankan `php artisan optimize:clear`.

## 15. Perintah Harian yang Sering Dipakai

```bash
php artisan serve
npm run dev
php artisan queue:work
php artisan optimize:clear
```
