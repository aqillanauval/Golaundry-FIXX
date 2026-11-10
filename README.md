# 🧺 GOLaundry

**Aplikasi laundry antar-jemput buat anak kos.** Pelanggan tinggal pesan lewat web, kurir jemput & nimbang cuciannya,
terus pelanggan bisa pantau status cuciannya sampai beres.

Project mata kuliah **Pemrograman Web** · **Basis Data** · **Teknologi Sistem Terintegrasi**

> PHP Native · PDO · Supabase (PostgreSQL) · OOP · MVC · AJAX

---

## ✨ Fiturnya apa aja?

| 👤 Pelanggan | 🧑‍🔧 Admin / Staff |
|---|---|
| Daftar akun & login | Dashboard ringkasan hari ini |
| Bikin pesanan (pilih layanan + alamat jemput) | Isi berat cucian yang udah dijemput |
| Pakai promo / diskon | Ganti status pesanan **tanpa reload** (AJAX) |
| Pantau status cucian kayak tiket | Catat pelanggan yang dateng ke kios |
| Riwayat + kasih ulasan bintang ⭐ | CRUD layanan, promo (+ upload foto), pelanggan, karyawan |
| Cetak nota / simpan PDF | Cari transaksi, liat log aktivitas, impor data lama |
| Ubah profil & hapus akun | |

**Akun buat nyoba:**

| Role | Email | Password |
|---|---|---|
| Admin | `admin@golaundry.com` | `admin123` |
| Pelanggan | `pelanggan@golaundry.com` | `pelanggan123` |

---

## 🚀 Cara jalanin

### 1. Siapin database di Supabase

1. Buka [supabase.com](https://supabase.com) → **New project**
   - Isi **Database Password**, terus **catat** (nanti dipakai)
   - Region pilih **Southeast Asia (Singapore)** biar cepet
2. Masuk ke menu **SQL Editor** → **New query** → copy-paste semua isi `database/supabase.sql` → klik **Run**
3. Cek di **Table Editor**, harusnya udah ada 6 tabel: `users`, `layanan`, `karyawan`, `promo`, `pesanan`, `ulasan`

### 2. Isi pengaturan koneksi

1. Copy file `app/config/database.contoh.php` → kasih nama **`database.php`** (di folder yang sama)
2. Di Supabase klik tombol **Connect** (di atas) → pilih **Session pooler**. Nanti muncul kayak gini:
   ```
   postgresql://postgres.abcdefghijkl:[YOUR-PASSWORD]@aws-1-ap-southeast-1.pooler.supabase.com:5432/postgres
   ```
3. Masukin ke `database.php`:
   ```php
   $host = 'aws-1-ap-southeast-1.pooler.supabase.com';
   $user = 'postgres.abcdefghijkl';   // pakai titik + kode project!
   $pass = 'password database kamu';
   ```

> **Kenapa harus "Session pooler"?** Koneksi langsung Supabase (`db.xxx.supabase.co`) cuma bisa lewat IPv6,
> padahal wifi kos / kampus biasanya masih IPv4. Session pooler bisa IPv4 dan cocok buat PDO.

> 🔒 `database.php` udah masuk `.gitignore`, jadi password kamu nggak ikut ke-push ke GitHub.
> Kalau file itu **terlanjur** pernah ke-commit, jalanin sekali: `git rm --cached app/config/database.php`

### 3. Nyalain PostgreSQL di XAMPP

1. XAMPP Control Panel → Apache **Config** → **PHP (php.ini)**
2. Cari 2 baris ini, terus **hapus titik koma (;)** di depannya:
   ```
   extension=pdo_pgsql
   extension=pgsql
   ```
3. Save → **Stop** terus **Start** lagi Apache

### 4. Buka aplikasinya

1. Taruh folder `PemrogramanWeb` di `C:\xampp\htdocs\`
2. Buka **http://localhost/PemrogramanWeb/** → login pakai akun admin
3. *(Sekali aja)* Pindahin data lama dari file `.txt`: menu **Log Aktivitas** → **Impor data lama (.txt)** → **Impor sekarang**

---

## 🗂️ Struktur folder (MVC)

```
PemrogramanWeb/
├── index.php, login.php, layanan.php, ...   ← "pintu masuk" tiap halaman
├── app/
│   ├── init.php           ← dipanggil paling atas: session + require semua file
│   ├── koneksi.php        ← bikin $pdo (koneksi ke Supabase)
│   ├── fungsi.php         ← fungsi kecil: e(), rupiah(), token form, dll
│   ├── log.php            ← catat aktivitas ke file (fopen, flock, fwrite, fgets)
│   ├── config/
│   │   ├── database.contoh.php   ← contoh pengaturan
│   │   └── database.php          ← pengaturan asli kamu (nggak di-upload)
│   ├── models/            ← M: urusan database (SELECT, INSERT, UPDATE, DELETE)
│   ├── views/             ← V: tampilan HTML
│   └── controllers/       ← C: ngatur alur (baca input → panggil model → tampilin view)
├── database/
│   ├── supabase.sql       ← bikin tabel di Supabase
│   └── mysql.sql          ← cadangan kalau mau balik ke MySQL XAMPP
├── assets/                ← CSS, JavaScript, gambar
├── uploads/promo/         ← foto promo hasil upload
├── storage/logs/          ← file log aktivitas
└── data/                  ← file .txt versi lama (buat diimpor)
```

### Alur 1 halaman (kayak sequence diagram di Modul 12)

```mermaid
sequenceDiagram
    actor U as User
    participant P as layanan.php
    participant C as LayananController
    participant M as LayananModel
    participant DB as Supabase
    participant V as views/layanan/index.php

    U->>P: buka halaman
    P->>C: new LayananController() → invoke()
    C->>C: harusLogin('admin')
    C->>M: semua()
    M->>DB: SELECT * FROM layanan
    DB-->>M: data layanan
    M-->>C: array data
    C->>V: tampilkan('layanan/index', data)
    V-->>U: halaman HTML
```

### Class & pewarisan (OOP)

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
    Admin : +getMenu()
    Admin : +getNamaRole()
    Pelanggan : +getMenu()
    Pelanggan : +getNamaRole()

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

---

## 🧩 Rancangan database (ERD)

```mermaid
erDiagram
    users ||--o{ pesanan : "pesan"
    layanan ||--o{ pesanan : "dipakai di"
    promo ||--o{ pesanan : "dipakai di"
    pesanan ||--o| ulasan : "punya"

    users {
        int id PK
        varchar username UK
        varchar password "hash"
        varchar nama
        varchar role "admin / customer"
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
        smallint diskon "1-100"
        date berlaku_sampai
        varchar foto
    }
    pesanan {
        int id PK
        varchar kode UK "No. Nota"
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
        smallint rating "1-5"
        varchar komentar
    }
    karyawan {
        int id PK
        varchar nama
        varchar jabatan
        varchar no_hp
    }
```

Beberapa catatan soal desain tabelnya:
- **Akun pelanggan dihapus → riwayat pesanannya tetap ada.** Ini karena `user_id` pakai `ON DELETE SET NULL`.
- **Harga & nama layanan disalin ke tabel pesanan.** Jadi kalau admin ganti harga, nota lama nggak ikut berubah.
- **Satu pesanan cuma bisa punya 1 ulasan.** `ulasan.pesanan_id` dibikin `UNIQUE`.
- **`CHECK` jagain isi kolom.** Contohnya rating cuma boleh 1–5, diskon 0–100, dan status cuma 5 pilihan.
- **RLS dinyalain di semua tabel.** Data nggak bisa diintip lewat API publik Supabase, tapi PHP tetap bisa akses.

---

## 📚 Materi kuliah yang dipakai

| Materi | Dipakai di mana |
|---|---|
| **Koneksi PDO** (DSN, `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `try/catch` + `error_log`) | `app/koneksi.php` |
| **`query()`** buat SQL tanpa input user | `LayananModel::semua()`, `jumlahPelanggan()` |
| **Prepared statement** `prepare()` → `execute()` | semua file di `app/models/` |
| **Fetch mode**: `FETCH_ASSOC`, `FETCH_CLASS`, `fetchColumn()` | `Model.php`, `PesananModel.php` (objek `Pesanan`) |
| **INSERT + `lastInsertId()`**, UPDATE/DELETE **selalu pakai WHERE**, `rowCount()` | `UserModel::tambah()`, `PesananModel::isiBerat()` |
| **Data kembar (UNIQUE)** → kode error 23000 / 23505 | `UserModel::tambah()` |
| **Pencarian aman pakai LIKE** (anti SQL injection) | `PesananModel::semua()` + kotak cari di menu Transaksi |
| **Transaksi** `beginTransaction()` / `commit()` / `rollBack()` | `UserModel::hapusAkunPelanggan()`, `ImporModel::jalankan()` |
| **Tugas**: Registrasi, Login, Ubah Profil, Hapus Akun + `password_hash()` / `password_verify()` | `AuthController`, `ProfileController`, `Pengguna::cekPassword()` |
| **Operasi File** (Modul 9): `fopen` (r/a/w), `flock`, `fwrite`, `fgets`, `fclose`, `file_exists`, `filesize`, `mkdir`, `explode` | `app/log.php`, `ImporModel::bacaTxt()`, upload foto di `PromoModel` |
| **Database** (Modul 11): koneksi di file terpisah + `require` | `app/koneksi.php` dipanggil dari `app/init.php` |
| **OOP** (Modul 12): class, `public`/`private`/`protected`, getter/setter, `extends`, override | `Pengguna` → `Admin` / `Pelanggan`, `Model` → `*Model`, `Controller` → `*Controller` |
| **MVC** (Modul 12): model, view, controller, `invoke()` | folder `app/models`, `app/views`, `app/controllers` |
| **Sistem terintegrasi**: PHP ↔ database cloud, JavaScript ↔ PHP lewat JSON | `koneksi.php` (Supabase), `dashboard-admin.js` + `DashboardAdminController::ubahStatus()` |

---

## 🛡️ Keamanan yang udah dipasang

- **Prepared statement.** Input user nggak pernah disambung langsung ke SQL.
- **Password di-hash.** Disimpan pakai `password_hash()`, jadi nggak ada password asli di database.
- **Token form (CSRF).** Setiap form punya token acak, jadi form palsu dari web lain bakal ditolak.
- **Error database disembunyiin.** Detailnya cuma dicatat di log server (`C:\xampp\php\logs\php_error_log`).
- **Folder penting dikunci.** `app/`, `database/`, `storage/`, dan `data/` nggak bisa dibuka dari browser (`.htaccess`).
- **RLS aktif di Supabase.**

---

## 🆘 Kalau error

| Muncul tulisan | Solusinya |
|---|---|
| `File app/config/database.php belum ada` | Copy dulu `database.contoh.php` jadi `database.php` |
| `could not find driver` | `extension=pdo_pgsql` belum dinyalain (lihat langkah 3). Kalau udah tapi masih error, tambahin `LoadFile "C:/xampp/php/libpq.dll"` di **Config → Apache (httpd-xampp.conf)** terus restart Apache |
| Loading lama terus gagal | Host-nya masih `db.xxx.supabase.co`. Ganti pakai host **Session pooler** |
| `password authentication failed` | User harus `postgres.kodeproject` (ada titiknya). Password yang dipakai itu **Database Password**, bukan API key |
| `Tenant or user not found` | Host / kode project salah ketik. Copy ulang dari tombol **Connect** |
| `relation "users" does not exist` | `database/supabase.sql` belum di-Run di SQL Editor |
| Tiba-tiba nggak bisa konek setelah lama nggak dibuka | Project Supabase gratis bisa ke-*pause*. Buka dashboard Supabase → **Restore** |

### Mau balik pakai MySQL XAMPP?
Import `database/mysql.sql` lewat phpMyAdmin, terus di `database.php` ganti jadi
`$jenisDb = 'mysql'; $host = '127.0.0.1'; $port = '3306'; $namaDb = 'golaundry'; $user = 'root'; $pass = '';`
Kode lainnya nggak perlu diubah. Itulah enaknya PDO 😎
