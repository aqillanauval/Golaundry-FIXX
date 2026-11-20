# GOLaundry (Laravel) — Panduan

Aplikasi laundry antar-jemput untuk mahasiswa dan anak kos.
Migrasi dari proyek PHP Native `PemrogramanWeb` ke Laravel + MariaDB (XAMPP).

Akun percobaan (dibuat oleh seeder):

| Role | Email | Password |
|---|---|---|
| Staff | admin@golaundry.com | admin123 |
| Pelanggan | pelanggan@golaundry.com | pelanggan123 |

---

## 1. Cek sekali sebelum memasang

Buka CMD lalu jalankan:

```
php -v
C:\xampp\php\php.exe -v
php -m | findstr /i "pdo_mysql mbstring openssl fileinfo curl"
```

- Proyek ini memakai **Laravel 13, yang butuh PHP 8.3 atau lebih baru**.
- `php` di CMD kamu adalah `C:\tools\php83` (versi 8.3.0). Itu yang dipakai Composer dan `php artisan`.
- Perintah ketiga harus menampilkan kelima ekstensi. Kalau ada yang tidak muncul, buka
  `C:\tools\php83\php.ini`, hapus tanda `;` di depan baris `extension=...` yang kurang, lalu tutup dan buka lagi CMD.
- Kalau `C:\xampp\php\php.exe -v` menunjukkan **8.2 atau lebih lama**, Apache XAMPP tidak bisa menjalankan
  Laravel 13. Jalankan aplikasi dengan `php artisan serve` (langkah 2.6). MariaDB dan phpMyAdmin dari XAMPP tetap dipakai.

## 2. Cara memasang

**2.1 Ekstrak** ZIP ke `C:\xampp\htdocs\golaundry-laravel`.
Folder lama `PemrogramanWeb` tidak diubah sama sekali, simpan sebagai cadangan.

**2.2 Pasang library Laravel** (butuh internet, sekitar 1-3 menit):

```
cd C:\xampp\htdocs\golaundry-laravel
composer install
```

**2.3 Siapkan file `.env`:**

```
copy .env.example .env
php artisan key:generate
```

Buka `.env` di VS Code. Bagian database sudah diisi bawaan XAMPP
(`DB_USERNAME=root`, `DB_PASSWORD=` kosong). Ubah kalau MariaDB-mu memakai password.

**2.4 Buat database:** XAMPP Control Panel → Start **Apache** dan **MySQL** →
buka `http://localhost/phpmyadmin` → **New** → nama `golaundry_laravel`,
collation `utf8mb4_unicode_ci` → **Create**.
Database lama `golaundry` tidak disentuh.

**2.5 Buat tabel + data awal:**

```
php artisan migrate --seed
```

Hasilnya 6 tabel GOLaundry (`users`, `layanan`, `karyawan`, `promo`, `pesanan`, `ulasan`)
ditambah tabel `migrations` milik Laravel.
Kalau perintah ini error, ada cadangan: impor `database/sql/golaundry_laravel.sql` lewat phpMyAdmin.

**2.6 Jalankan:**

```
php artisan serve
```

Buka `http://localhost:8000` di Chrome. Biarkan CMD tetap terbuka selama aplikasi dipakai.

Alternatif lewat Apache (hanya kalau PHP XAMPP ≥ 8.3):
`http://localhost/golaundry-laravel/public`, lalu ubah `APP_URL` di `.env` ke alamat itu.

## 3. Masalah yang sering muncul

| Pesan error | Penyebab dan solusi |
|---|---|
| `could not find driver` | `extension=pdo_mysql` belum aktif di php.ini (lihat bagian 1) |
| `SQLSTATE[HY000] [2002]` / connection refused | MySQL di XAMPP belum di-Start |
| `SQLSTATE[HY000] [1045] Access denied` | Isi `DB_USERNAME` / `DB_PASSWORD` di `.env` salah |
| `Unknown database 'golaundry_laravel'` | Langkah 2.4 belum dilakukan |
| `No application encryption key` | `php artisan key:generate` belum dijalankan |
| `Your requirements could not be resolved` saat composer install | Versi PHP di bawah 8.3. Kirim pesan error lengkapnya |
| Halaman 419 "kedaluwarsa" | Form dibiarkan terlalu lama. Refresh lalu kirim ulang |
| Perubahan `.env` tidak terbaca | `php artisan config:clear` |

---

## 4. Struktur proyek (yang perlu dijelaskan saat presentasi)

```
app/
├── Models/                 MODEL: satu class per tabel
│   ├── User.php            akun staff & pelanggan, menu sidebar per role, hapusAkunPelanggan()
│   ├── Pesanan.php         subtotal(), potongan(), total(), daftar status
│   ├── Layanan.php  Promo.php  Karyawan.php  Ulasan.php
├── Http/Controllers/       CONTROLLER: mengatur alur tiap halaman
│   ├── AuthController.php        login, registrasi, logout
│   ├── ProfileController.php     lihat / ubah / hapus profil
│   ├── DashboardController.php   dashboard staff & pelanggan + data grafik
│   ├── LayananController, PromoController, KaryawanController, PelangganController   (CRUD staff)
│   ├── TransaksiController.php   transaksi kios, isi berat, ubah status (AJAX)
│   ├── PesananController.php     pelanggan membuat / mengubah / membatalkan pesanan
│   ├── RiwayatController.php     riwayat + ulasan pelanggan
│   ├── UlasanController.php      staff melihat & menghapus ulasan
│   └── NotaController.php        nota + cetak
├── Http/Middleware/
│   ├── CekRole.php         membatasi halaman staff / pelanggan
│   └── TanpaCache.php      tombol Back tidak bisa membuka halaman setelah logout
└── helpers.php             rupiah(), formatBerat(), tanggalIndo(), ... (pindahan fungsi.php)

bootstrap/app.php           mendaftarkan middleware 'role' dan 'tanpa.cache'
routes/web.php              semua alamat halaman, dikelompokkan per hak akses
database/migrations/        struktur 6 tabel (+ CHECK constraint)
database/seeders/           data awal
resources/views/            VIEW (Blade)
├── layouts/app.blade.php   template utama: sidebar, topbar, flash, modal hapus
├── layouts/auth.blade.php  template login & registrasi
└── (satu folder per fitur)
lang/id/validation.php      pesan validasi bahasa Indonesia
public/css/style.css        semua tampilan (tanpa framework CSS)
public/js/                  app.js, dashboard-staff.js (AJAX), pesanan.js (hitung harga)
public/uploads/promo/       foto promo hasil upload (.htaccess melarang file .php jalan)
public/vendor/              font & ikon disimpan lokal → tetap tampil tanpa internet
```

**Alur satu permintaan (contoh: buka halaman Layanan)**

```
Browser → routes/web.php (/layanan)
        → middleware auth (sudah login?) → role:admin (staff?) → tanpa.cache
        → LayananController@index
        → Model Layanan::withCount('pesanan')->get()   (query ke MariaDB)
        → view layanan/index.blade.php  → HTML ke browser
```

Ini sama dengan pola proyek lama (`layanan.php` → `LayananController->invoke()` → model → view).
Bedanya, sekarang routing, pengecekan login, dan CSRF sudah ditangani Laravel.

## 5. Dari PHP Native ke Laravel

| Proyek lama | Laravel | Materi |
|---|---|---|
| `new PDO(...)` di koneksi.php | `.env` + Eloquent (di belakangnya tetap PDO + prepared statement) | Database / PDO |
| `$stmt->prepare(... :id)` | `Layanan::find($id)`, `where('kode', $kode)` | SQL Injection |
| `harusLogin('admin')` | `->middleware(['auth', 'role:admin'])` | Session, MVC |
| `$_SESSION['user_id']` | `Auth::user()` | Session |
| `session_destroy()` | `Auth::logout()` + `session()->invalidate()` | Session |
| `setcookie('ingat_user', ...)` | `Cookie::queue('ingat_user', ...)` | Cookie |
| `password_hash` / `password_verify` | `Hash::make` / `Auth::attempt` | Keamanan |
| `inputToken()` + `tokenCocok()` | `@csrf` | Form |
| `e($teks)` / `htmlspecialchars` | `{{ $teks }}` | XSS |
| `$_SESSION['flash']` | `->with('sukses', '...')` + `session('sukses')` | Flash data (Modul 14) |
| validasi `if` manual | `$request->validate([...])` | Form, Validation (Modul 14) |
| `header.php` + `footer.php` | `@extends('layouts.app')` + `@section` | Blade (Modul 13-14) |
| `$pdo->beginTransaction()` | `DB::transaction(function () {...})` | Transaksi DB |
| `$_FILES` + `move_uploaded_file` | `$request->file('foto')->move(...)` | File Handling |
| `unlink()` foto lama | `unlink()` tetap sama di `PromoController` | Operasi File |
| `fetch()` + JSON | tetap `fetch()` + header `X-CSRF-TOKEN` | JavaScript & AJAX |
| `class Admin extends Pengguna` | `class Layanan extends Model` | OOP |

## 6. Aturan bisnis di database

- `pesanan.user_id` → `ON DELETE SET NULL`: akun pelanggan dihapus, riwayat transaksinya tetap ada.
- `nama_layanan`, `harga`, `nama_promo`, `diskon` **disalin** ke pesanan, jadi nota lama tidak berubah saat harga diganti.
- `ulasan.pesanan_id` UNIQUE: satu pesanan maksimal satu ulasan. `ON DELETE CASCADE`: pesanan dihapus, ulasannya ikut terhapus.
- CHECK constraint: rating 1-5, diskon promo 1-100, harga > 0, berat ≥ 0, status hanya 5 nilai, role hanya `admin`/`customer`.
- Status: **Menunggu Jemput → Diterima → Dicuci → Selesai → Diambil**.
  "Menunggu Jemput" hanya untuk pesanan dari aplikasi sebelum berat diisi staff.
  Pelanggan tidak bisa mengubah status. Status hanya diubah lewat route khusus staff.

## 7. Daftar uji sesuai rubrik

Centang setelah dicoba sendiri, lalu ambil screenshot sebagai bukti.

**Kelompok**

- [ ] Registrasi akun baru → muncul "Akun berhasil dibuat" dan data ada di tabel `users` (phpMyAdmin)
- [ ] Registrasi dengan email yang sama → "Email / no. HP ini sudah terdaftar."
- [ ] Registrasi password < 6 / konfirmasi beda / email salah format → pesan error jelas
- [ ] Login benar → masuk dashboard, nama tampil di sapaan dan pojok kanan atas
- [ ] Login password salah → "Email/No. HP atau password salah."
- [ ] Logout → kembali ke login dengan pesan "Kamu sudah logout"
- [ ] Buka `http://localhost:8000/dashboard` tanpa login → dilempar ke login + pesan
- [ ] Setelah logout tekan tombol **Back** Chrome → tidak menampilkan dashboard (diarahkan ke login)
- [ ] Profil: lihat data, ubah nama/email, ganti password (wajib isi password lama), hapus akun (pelanggan, wajib password)
- [ ] **SQL Injection**: login dengan email `' OR '1'='1` dan password `' OR '1'='1` → ditolak
- [ ] **XSS**: ubah nama profil jadi `<script>alert('xss')</script>` → tampil sebagai teks, tidak ada popup
- [ ] Login sebagai pelanggan lalu buka `/layanan` → ditolak: "Kamu tidak punya akses ke halaman itu."
- [ ] Chrome DevTools (Ctrl+Shift+M) ukuran iPhone → sidebar jadi tombol menu, tabel bisa digeser

**Individu (ulangi untuk setiap modul)**

- [ ] Tambah, lihat, edit, hapus data → tersimpan di phpMyAdmin
- [ ] Kosongkan field wajib / isi angka tidak valid → pesan error
- [ ] Klik hapus → muncul jendela konfirmasi dulu
- [ ] Isi field teks dengan `<script>alert(1)</script>` → tampil sebagai teks
- [ ] Isi kolom pencarian dengan `' OR '1'='1` → tidak membocorkan data
- [ ] Buka URL modul tanpa login / setelah logout → ditolak

**Usulan pembagian 6 modul individu** (masing-masing lengkap CRUD + validasi + konfirmasi hapus):

| Modul | Halaman | Controller |
|---|---|---|
| 1. Layanan | /layanan | LayananController |
| 2. Promo + upload foto | /promo | PromoController |
| 3. Karyawan | /karyawan | KaryawanController |
| 4. Pelanggan (oleh staff) | /pelanggan | PelangganController |
| 5. Pesanan pelanggan + transaksi kios | /pesanan, /transaksi | PesananController, TransaksiController |
| 6. Ulasan (pelanggan beri/ubah/hapus, staff lihat/hapus) | /riwayat, /ulasan | RiwayatController, UlasanController |

Catatan: total rincian rubrik individu adalah 34 poin, bukan 50. Ini perlu dikonfirmasi ke dosen.
