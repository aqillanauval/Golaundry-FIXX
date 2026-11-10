# GOLaundry – versi Database (Supabase) + OOP + MVC

Aplikasi manajemen laundry dengan PHP native. Data yang sebelumnya disimpan di file `.txt`
sekarang tersimpan di database **PostgreSQL Supabase** dan diakses lewat **PDO**.
Kodenya disusun dengan pola **MVC** (Model – View – Controller).

Akun contoh (dibuat oleh `database/supabase.sql`):

| Peran     | Email                     | Password       |
|-----------|---------------------------|----------------|
| Admin     | admin@golaundry.com       | admin123       |
| Pelanggan | pelanggan@golaundry.com   | pelanggan123   |

---

## 1. Menyiapkan Supabase

1. Masuk ke <https://supabase.com>, lalu klik **New project**.
   - Isi **Database Password**, lalu **simpan password ini** karena akan dipakai di langkah 3.
   - Region: pilih **Southeast Asia (Singapore)** supaya koneksi dari Indonesia lebih cepat.
2. Buka menu **SQL Editor**, lalu **New query**. Tempel seluruh isi `database/supabase.sql`, lalu klik **Run**.
   File ini membuat 6 tabel, mengaktifkan RLS, dan mengisi data awal.
   Hasilnya bisa dilihat di menu **Table Editor**.
3. Klik tombol **Connect** di bagian atas, lalu pilih **Session pooler**. Contoh string yang muncul:

   ```
   postgresql://postgres.abcdefghijkl:[YOUR-PASSWORD]@aws-1-ap-southeast-1.pooler.supabase.com:5432/postgres
   ```

   Salin bagian-bagiannya ke `app/config/database.php`:

   ```php
   'driver'  => 'pgsql',
   'host'    => 'aws-1-ap-southeast-1.pooler.supabase.com',  // host dari string di atas
   'port'    => '5432',
   'dbname'  => 'postgres',
   'user'    => 'postgres.abcdefghijkl',                     // ada titik + project ref
   'pass'    => 'password database dari langkah 1',
   'sslmode' => 'require',
   ```

   > **Kenapa Session pooler?** Koneksi langsung (`db.xxxx.supabase.co`) hanya bisa lewat IPv6,
   > sedangkan jaringan rumah dan kampus umumnya masih IPv4. Session pooler mendukung IPv4
   > dan prepared statement, jadi cocok untuk PHP + PDO.

## 2. Mengaktifkan PostgreSQL di XAMPP (Windows)

PDO butuh driver `pdo_pgsql`. Driver ini sudah ada di XAMPP, tetapi belum aktif.

1. Buka XAMPP Control Panel. Pada baris Apache, klik **Config**, lalu pilih **PHP (php.ini)**.
2. Cari kedua baris berikut dan **hapus tanda `;`** di depannya:
   ```
   extension=pdo_pgsql
   extension=pgsql
   ```
3. Simpan file, lalu **Stop** dan **Start** Apache.
4. Cek dengan membuat file `info.php` berisi `<?php phpinfo();` di htdocs. Halamannya harus
   menampilkan bagian **pdo_pgsql**. Hapus `info.php` setelah selesai dicek.

Kalau masih muncul *could not find driver*, buka **Config → Apache (httpd-xampp.conf)**, tambahkan
baris berikut di bagian atas, lalu restart Apache:
```
LoadFile "C:/xampp/php/libpq.dll"
```

## 3. Menjalankan aplikasi

1. Taruh folder `PemrogramanWeb` di `C:\xampp\htdocs\`.
2. Buka <http://localhost/PemrogramanWeb/>, lalu login sebagai admin.
3. **Memindahkan data lama dari file .txt** (cukup sekali): buka menu **Log Aktivitas**, pilih
   **Impor data lama (.txt)**, lalu klik **Impor sekarang**. Data yang sudah ada otomatis dilewati.

### Masih ingin memakai MySQL XAMPP?
Impor `database/mysql.sql` lewat phpMyAdmin, lalu ubah `app/config/database.php` menjadi
`'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => '3306', 'dbname' => 'golaundry', 'user' => 'root', 'pass' => ''`.
Kode model tidak perlu diubah sama sekali, karena itulah kelebihan PDO.

---

## 4. Struktur folder (MVC)

```
PemrogramanWeb/
├── index.php, login.php, layanan.php, ...   FRONT CONTROLLER: hanya new XxxController()->invoke()
├── app/
│   ├── bootstrap.php           session, zona waktu, autoload kelas, error handler
│   ├── config/database.php     pengaturan koneksi (Supabase / MySQL)
│   ├── core/
│   │   ├── Database.php        koneksi PDO (singleton) + try/catch + error_log
│   │   ├── Model.php           kelas induk semua model (prepare/execute, cari, hapus)
│   │   ├── Controller.php      kelas induk semua controller (view, redirect, wajibLogin, CSRF)
│   │   ├── Logger.php          log aktivitas ke FILE (fopen/flock/fwrite/fgets)
│   │   └── helpers.php         fungsi kecil untuk tampilan (e, rupiah, ikon, ...)
│   ├── entities/               OBJEK DATA
│   │   ├── Pengguna.php        abstract: induk Admin & Pelanggan
│   │   ├── Admin.php           extends Pengguna
│   │   ├── Pelanggan.php       extends Pengguna
│   │   └── Pesanan.php         dipakai PDO::FETCH_CLASS, berisi subtotal()/total()
│   ├── models/          M      UserModel, LayananModel, KaryawanModel, PromoModel,
│   │                           PesananModel, UlasanModel, ImporModel
│   ├── controllers/     C      AuthController, LayananController, OrderController, ...
│   └── views/           V      HTML per halaman (layout/, layanan/, order/, ...)
├── database/
│   ├── supabase.sql            skema PostgreSQL (dipakai)
│   └── mysql.sql               skema MySQL/MariaDB (cadangan)
├── storage/logs/aktivitas.log  log aktivitas (dibuat otomatis)
├── uploads/promo/              foto promo hasil upload
├── data/                       file .txt lama (sumber impor)
└── assets/                     css, js, gambar (tidak berubah)
```

Alur satu request, misalnya membuka **layanan.php**:

```
Browser → layanan.php → new LayananController() → invoke()
            → wajibLogin('admin')                     (cek SESSION + CSRF)
            → LayananModel->semua()                   (SELECT ... lewat PDO)
            → $this->view('layanan/index', [...])     (HTML di app/views/layanan/index.php)
```

## 5. Rancangan tabel (ERD singkat)

```
users ──1───n── pesanan ──1───0..1── ulasan
                 │   │
layanan ──1───n──┘   └──n───1── promo
karyawan (berdiri sendiri)
```

* `pesanan.user_id` mengacu ke `users.id` dengan `ON DELETE SET NULL`. Kalau akun pelanggan
  dihapus, riwayat pesanannya tetap tersimpan.
* `pesanan` juga menyimpan **salinan** nama layanan, harga, nama promo, dan diskon. Dengan begitu
  nota lama tidak ikut berubah saat admin mengganti harga atau menghapus promo.
* `ulasan.pesanan_id` memakai `UNIQUE` (satu pesanan hanya punya satu ulasan) dan
  `ON DELETE CASCADE` (ulasan ikut terhapus bersama pesanannya).
* `CHECK` dipakai untuk menjaga nilai yang boleh masuk: status, rating 1–5, diskon 0–100, dan role.

## 6. Pemetaan materi → kode

| Materi | Diterapkan di |
|---|---|
| **09 Database / 10 Panduan CRUD PDO** – koneksi PDO, DSN, `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, try/catch + `error_log()` | `app/core/Database.php` |
| `query()` untuk SQL tanpa input, `prepare()` + `execute()` untuk SQL dengan input | `app/core/Model.php` dan semua file `app/models/*` |
| Fetch mode: `FETCH_ASSOC` (layanan, promo, ...), `FETCH_CLASS` (Pesanan), `fetchColumn()` (hitung) | `Model.php`, `PesananModel.php` |
| INSERT + `lastInsertId()`, UPDATE/DELETE selalu dengan `WHERE`, `rowCount()` | `UserModel::tambah()`, `PesananModel::isiBerat()`, `batalkan()` |
| Duplikat UNIQUE (SQLSTATE 23000/23505) | `Database::isDuplikat()`, `UserModel::tambah()` |
| Pencarian aman dengan `LIKE` + parameter (demo SQL injection gagal) | `PesananModel::semua()`, kotak cari di **Transaksi** |
| **Transaksi** `beginTransaction()` / `commit()` / `rollBack()` | `UserModel::hapusAkunPelanggan()`, `ImporModel::jalankanImpor()` |
| Tugas: tabel users, login dengan prepared statement, `password_hash()`/`password_verify()`, Registrasi, Login, Ubah Profil, **Hapus Akun** | `AuthController`, `ProfileController`, `Pengguna::cekPassword()` |
| **Modul 9 Operasi File** – `fopen` (mode r/a/w), `flock(LOCK_EX)`, `fwrite`, `fgets`, `fclose`, `file_exists`, `filesize`, `mkdir`, `explode` | `app/core/Logger.php` (log aktivitas), `ImporModel::bacaTxt()`, upload foto di `PromoModel` |
| **Modul 11 Database** – koneksi terpisah yang di-*require*, CRUD ke tabel, PDO | `app/config/database.php` + `Database.php`, semua model |
| **Modul 12 OOP** – class, `public`/`private`/`protected`, getter/setter, `extends`, `abstract`, polimorfisme | `Pengguna` → `Admin`/`Pelanggan` (`menu()`, `urlDashboard()` berbeda), `Model` → `*Model`, `Controller` → `*Controller` |
| **Modul 12 MVC** – model, view, controller, dan `index.php` yang memanggil `invoke()` | `app/models`, `app/views`, `app/controllers`, file `.php` di root |

## 7. Keamanan yang sudah ada

* Semua query yang membawa input memakai **prepared statement**.
* Password disimpan sebagai **hash** dengan `password_hash()`.
* **RLS aktif** di semua tabel Supabase, jadi data tidak bisa dibaca lewat API publik Supabase.
  PHP tetap bisa mengakses data karena login sebagai pemilik tabel.
* Setiap form POST membawa **token CSRF**.
* Detail error database **tidak ditampilkan** ke pengguna, hanya dicatat di log server
  (XAMPP: `C:\xampp\php\logs\php_error_log`).
* Folder `app/`, `database/`, `storage/`, dan `data/` diblokir dari browser lewat `.htaccess`.
* Jangan unggah password Supabase asli ke repositori publik. Bisa juga diisi lewat
  environment variable `DB_PASS`.

## 8. Masalah yang sering muncul

| Gejala | Penyebab & solusi |
|---|---|
| `could not find driver` | `pdo_pgsql` belum aktif. Lihat bagian 2. |
| Loading lama lalu gagal / `timeout` | Kemungkinan host yang dipakai masih `db.xxx.supabase.co` (IPv6). Ganti ke host **Session pooler**. |
| `password authentication failed` | User harus `postgres.<project-ref>` (pakai titik), bukan `postgres` saja. Password yang dipakai adalah **Database Password**, bukan API key. |
| `Tenant or user not found` | Host pooler atau project ref salah. Salin ulang dari tombol **Connect**. |
| `relation "users" does not exist` | `database/supabase.sql` belum dijalankan di SQL Editor. |
| Tiba-tiba tidak bisa konek setelah lama tidak dipakai | Project Supabase gratis bisa di-*pause*. Buka dashboard Supabase lalu klik **Restore**. |
