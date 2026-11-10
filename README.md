# GOLaundry

Aplikasi web laundry antar-jemput untuk anak kos. Pelanggan memesan lewat web, kurir menjemput dan menimbang cucian, lalu pelanggan bisa memantau status cuciannya sampai selesai.

Dibuat untuk tugas mata kuliah Pemrograman Web, Basis Data, dan Teknologi Sistem Terintegrasi.

## Fitur

Pelanggan:
- Daftar akun dan login
- Membuat pesanan (pilih layanan dan alamat jemput)
- Memakai promo
- Melihat status cucian
- Riwayat pesanan dan memberi ulasan
- Cetak nota
- Ubah profil dan hapus akun

Admin:
- Dashboard ringkasan hari ini
- Mengisi berat cucian yang sudah dijemput
- Mengubah status pesanan tanpa reload halaman
- Mencatat pelanggan yang datang langsung ke kios
- Kelola data layanan, promo (dengan foto), pelanggan, dan karyawan
- Mencari transaksi, melihat log aktivitas, dan impor data lama

Akun untuk mencoba:

| Role | Email | Password |
|---|---|---|
| Admin | admin@golaundry.com | admin123 |
| Pelanggan | pelanggan@golaundry.com | pelanggan123 |

## Cara Menjalankan

### 1. Buat database di Supabase

1. Buat project baru di supabase.com. Catat Database Password-nya. Region sebaiknya pilih Singapore.
2. Buka SQL Editor, tempel isi file `database/supabase.sql`, lalu klik Run.
3. Cek di Table Editor, seharusnya ada 6 tabel: `users`, `layanan`, `karyawan`, `promo`, `pesanan`, `ulasan`.

### 2. Atur koneksi database

1. Copy `app/config/database.contoh.php` menjadi `app/config/database.php`.
2. Di Supabase klik Connect, pilih Session pooler. Formatnya seperti ini:
   ```
   postgresql://postgres.abcdefghijkl:[YOUR-PASSWORD]@aws-1-ap-southeast-1.pooler.supabase.com:5432/postgres
   ```
3. Isi `database.php` sesuai data tersebut:
   ```php
   $host = 'aws-1-ap-southeast-1.pooler.supabase.com';
   $user = 'postgres.abcdefghijkl';
   $pass = 'password database';
   ```

Catatan: pakai Session pooler, bukan koneksi langsung (`db.xxx.supabase.co`), karena koneksi langsung hanya mendukung IPv6.

File `database.php` sudah dimasukkan ke `.gitignore` supaya password tidak ikut ter-upload.

### 3. Aktifkan PostgreSQL di XAMPP

1. Buka XAMPP Control Panel, klik Config pada Apache, pilih PHP (php.ini).
2. Hapus tanda `;` di depan baris berikut:
   ```
   extension=pdo_pgsql
   extension=pgsql
   ```
3. Simpan, lalu restart Apache.

### 4. Jalankan aplikasi

1. Simpan folder `PemrogramanWeb` di `C:\xampp\htdocs\`.
2. Buka `http://localhost/PemrogramanWeb/` dan login dengan akun admin.
3. Untuk memindahkan data lama dari file `.txt`, buka menu Log Aktivitas lalu pilih Impor data lama (cukup sekali).

## Struktur Folder

```
PemrogramanWeb/
├── index.php, login.php, ...   file halaman
├── app/
│   ├── init.php                session dan memanggil semua file
│   ├── koneksi.php             koneksi database (PDO)
│   ├── fungsi.php              fungsi bantuan
│   ├── log.php                 pencatatan log ke file
│   ├── config/                 pengaturan database
│   ├── models/
│   ├── views/
│   └── controllers/
├── database/
│   ├── supabase.sql
│   └── mysql.sql
├── assets/                     css, js, gambar
├── uploads/promo/              foto promo
├── storage/logs/               file log
└── data/                       data lama (.txt)
```

Alur halaman: file halaman (misalnya `layanan.php`) membuat objek controller lalu memanggil `invoke()`. Controller mengecek login, mengambil data lewat model, kemudian menampilkan view.

```mermaid
sequenceDiagram
    actor U as User
    participant P as layanan.php
    participant C as LayananController
    participant M as LayananModel
    participant DB as Supabase
    participant V as views/layanan/index.php

    U->>P: buka halaman
    P->>C: invoke()
    C->>C: harusLogin('admin')
    C->>M: semua()
    M->>DB: SELECT * FROM layanan
    DB-->>M: data
    M-->>C: array data
    C->>V: tampilkan('layanan/index', data)
    V-->>U: HTML
```

## Class Diagram

```mermaid
classDiagram
    class Pengguna {
        -id
        -username
        -nama
        -password
        #role
        +getNama()
        +cekPassword(p)
        +getMenu()
    }
    Pengguna <|-- Admin
    Pengguna <|-- Pelanggan

    class Model {
        #db
        #tabel
        +cari(id)
        +hapus(id)
    }
    Model <|-- UserModel
    Model <|-- LayananModel
    Model <|-- PromoModel
    Model <|-- PesananModel
    Model <|-- KaryawanModel
    Model <|-- UlasanModel

    class Controller {
        #tampilkan(view, data)
        #harusLogin(role)
        #pindahKe(halaman)
    }
    Controller <|-- LayananController
    Controller <|-- OrderController
    Controller <|-- TransaksiController
```

## ERD

```mermaid
erDiagram
    users ||--o{ pesanan : "pesan"
    layanan ||--o{ pesanan : "dipakai di"
    promo ||--o{ pesanan : "dipakai di"
    pesanan ||--o| ulasan : "punya"

    users {
        int id PK
        varchar username UK
        varchar password
        varchar nama
        varchar role
    }
    layanan {
        int id PK
        varchar nama
        int harga
        varchar satuan
    }
    promo {
        int id PK
        varchar judul
        smallint diskon
        date berlaku_sampai
        varchar foto
    }
    pesanan {
        int id PK
        varchar kode UK
        int user_id FK
        int layanan_id FK
        int promo_id FK
        numeric berat
        varchar status
        timestamp tanggal
    }
    ulasan {
        int id PK
        int pesanan_id FK
        smallint rating
        varchar komentar
    }
    karyawan {
        int id PK
        varchar nama
        varchar jabatan
        varchar no_hp
    }
```

Keterangan:
- Jika akun pelanggan dihapus, pesanannya tetap tersimpan (`user_id` memakai `ON DELETE SET NULL`).
- Nama dan harga layanan disalin ke tabel pesanan, jadi nota lama tidak berubah kalau harga diganti.
- Satu pesanan hanya bisa punya satu ulasan.
- Beberapa kolom dibatasi dengan `CHECK`, misalnya rating 1-5 dan diskon 0-100.

## Materi yang Dipakai

- Koneksi PDO dan try/catch: `app/koneksi.php`
- Prepared statement: semua file di `app/models/`
- Fetch mode (`FETCH_ASSOC`, `FETCH_CLASS`): `Model.php`, `PesananModel.php`
- Transaksi database: `UserModel::hapusAkunPelanggan()`, `ImporModel::jalankan()`
- Registrasi, login, ubah profil, hapus akun dengan `password_hash()`: `AuthController`, `ProfileController`
- Operasi file (Modul 9): `app/log.php`, `ImporModel`, upload foto di `PromoModel`
- OOP dan MVC (Modul 12): folder `app/models`, `app/views`, `app/controllers`
- AJAX dengan JSON: `dashboard-admin.js` dan `DashboardAdminController`

## Keamanan

- Query memakai prepared statement.
- Password disimpan dalam bentuk hash.
- Form memakai token CSRF.
- Pesan error database tidak ditampilkan ke user, hanya dicatat di log.
- Folder `app/`, `database/`, `storage/`, dan `data/` tidak bisa diakses dari browser (`.htaccess`).
- RLS aktif di Supabase.

## Masalah yang Sering Muncul

| Error | Solusi |
|---|---|
| `File app/config/database.php belum ada` | Copy `database.contoh.php` menjadi `database.php` |
| `could not find driver` | Aktifkan `pdo_pgsql` di php.ini. Kalau masih error, tambahkan `LoadFile "C:/xampp/php/libpq.dll"` di httpd-xampp.conf lalu restart Apache |
| Loading lama lalu gagal | Pastikan host memakai Session pooler |
| `password authentication failed` | Cek user (`postgres.kodeproject`) dan Database Password |
| `Tenant or user not found` | Host atau kode project salah |
| `relation "users" does not exist` | `supabase.sql` belum dijalankan |
| Tidak bisa konek setelah lama tidak dipakai | Project Supabase ter-pause, klik Restore di dashboard |

## Menggunakan MySQL XAMPP

Import `database/mysql.sql` lewat phpMyAdmin, lalu ubah `database.php` menjadi:

```php
$jenisDb = 'mysql';
$host    = '127.0.0.1';
$port    = '3306';
$namaDb  = 'golaundry';
$user    = 'root';
$pass    = '';
```