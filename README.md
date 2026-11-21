# GoLaundry

Aplikasi laundry antar-jemput untuk mahasiswa dan anak kos.

GOLaundry adalah website manajemen laundry kiloan berbasis Laravel dan menggunakan Database MariaDB. Pelanggan memesan laundry dari website, kurir menjemput cucian, lalu staff menimbang dan mencatat beratnya. Total harga dihitung otomatis dari berat, tarif layanan, dan promo. Pesanan dari website dan dari pelanggan yang datang langsung ke kios dicatat di satu sistem yang sama.

Merupakan project akhir mata kuliah Pemrograman Web A, Program Studi Pendidikan Teknologi Informasi, Fakultas Ilmu Komputer, Universitas Brawijaya, 2026.

## Alur Singkat
1. Pelanggan membuat pesanan: memilih layanan, mengisi alamat jemput, dan memilih promo (opsional).
2. Pesanan masuk dengan status Menunggu Jemput.
3. Kurir menjemput cucian, lalu staff menimbang dan mengisi beratnya. Total harga dihitung otomatis.
4. Staff memperbarui status: Diterima → Dicuci → Selesai → Diambil.
5. Pelanggan memantau status, membuka nota, dan memberi ulasan setelah cucian selesai.

## Fitur

### Pelanggan

| Fitur | Keterangan |
| --- | --- |
| Registrasi dan Login | Daftar dengan email atau nomor HP. Ada pilihan "Ingat saya" |
| Dashboard | Status pesanan yang sedang berjalan, promo aktif, dan pesanan terakhir |
| Buat Pesanan | Pilih layanan dan promo, isi alamat jemput. Pesanan masih bisa diubah atau dibatalkan sebelum dijemput |
| Riwayat Pesanan | Daftar semua pesanan dengan filter status |
| Ulasan | Beri rating 1–5 bintang dan komentar untuk pesanan yang sudah selesai |
| Nota | Lihat dan cetak nota pesanan sendiri |
| Profil | Ubah nama, email/No. HP, password, atau hapus akun |

### Staff

| Fitur | Keterangan |
| --- | --- |
| Dashboard | Ringkasan pesanan dan pendapatan, grafik 7/30 hari, daftar cucian yang perlu dijemput, ubah status tanpa memuat ulang halaman |
| Transaksi | Catat pelanggan yang datang ke kios, isi berat hasil timbangan, ubah/hapus transaksi, cari berdasarkan nota/nama/layanan |
| Layanan | Kelola jenis layanan, harga, dan satuan (kg, item, pasang) |
| Promo | Kelola diskon (%), masa berlaku, dan foto banner |
| Pelanggan | Buat, cari, ubah, dan hapus akun pelanggan |
| Karyawan | Kelola data karyawan (nama, jabatan, No. HP) |
| Ulasan | Lihat rata-rata rating, filter per bintang, hapus ulasan yang tidak pantas |
| Nota | Lihat dan cetak nota semua pesanan |


## Keamanan
1. Password disimpan dalam bentuk hash (bcrypt).
2. Hak akses staff dan pelanggan dibatasi dengan middleware role.
3. Proteksi CSRF di setiap form, dan query memakai Eloquent (aman dari SQL Injection).
4. Output di-escape oleh Blade, sehingga skrip di komentar tidak dijalankan (XSS).
5. Session diganti setelah login. Setelah logout, halaman lama tidak bisa dibuka lagi dengan tombol Back (middleware tanpa.cache).
6. Pelanggan hanya bisa membuka pesanan, nota, dan ulasan miliknya sendiri.

## Teknologi
1. PHP 8.3 dan Laravel 13
2. MariaDB (XAMPP)
3. Blade, CSS, dan JavaScript tanpa framework tambahan
4. Font Awesome 6.7.2 dan font Plus Jakarta Sans (disimpan lokal, tetap tampil tanpa internet)
5. Desain antarmuka: Figma


## Struktur Database

| Tabel | Isi |
| --- | --- |
| `users` | Akun staff (`admin`) dan pelanggan (`customer`) |
| `layanan` | Jenis layanan, harga, satuan |
| `promo` | Diskon, masa berlaku, foto |
| `pesanan` | Semua transaksi dari aplikasi dan kios. Berelasi ke `users`, `layanan`, `promo` |
| `ulasan` | Rating dan komentar. Satu pesanan maksimal satu ulasan |
| `karyawan` | Data karyawan |

Tabel `pesanan` menyimpan salinan nama layanan, harga, dan diskon saat transaksi dibuat, sehingga nota lama tidak berubah walaupun layanan atau promo diubah atau dihapus.

## Cara Menjalankan
Kebutuhan: PHP 8.3, Composer, dan XAMPP (Apache + MariaDB).
```
git clone https://github.com/aqillanauval/Golaundry-FIXX.git
cd Golaundry-FIXX
composer install
cp .env.example .env
php artisan key:generate
```

1. Buat database golaundry_laravel di phpMyAdmin (atau ganti DB_DATABASE di file .env).
2. Jalankan migration dan data awal:
```
php artisan migrate --seed
```
3. Jalankan server
```
php artisan serve
```

### Akun Demo

| Peran | Email | Password |
| --- | --- | --- |
| Staff | `admin@golaundry.com` | `admin123` |
| Pelanggan | `pelanggan@golaundry.com` | `pelanggan123` |

## Tim Pengembang (Kelompok 1)

| Nama | NIM | Fitur |
| --- | --- | --- |
| Aqilla Nauval Maulana | 255150600111047 | Manajemen Layanan Laundry |
| Intan Fitriah | 255150607111011 | Manajemen Pelanggan |
| Khalisa Nabalah | 255150601111028 | Transaksi dan Riwayat Pesanan |
| Mounttita Vania Acintya | 255150607111010 | Order/Pemesanan Laundry |
| Muhammad Ilham | 255150600111002 | Manajemen Karyawan |
| Muhammad Syahrial Alfadiaz | 255150600111027 | Manajemen Promo |

## **Dosen Pengampu:** Ir. Tri Afirianto, S.T., M.T.  
## **Asisten:** Ghefira Addien M. M. dan Aisha Maryam