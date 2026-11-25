# Catatan Perbaikan GOLaundry

## Bug yang diperbaiki
1. **Folder `app/models` diganti menjadi `app/Models`.**
   Namespace-nya `App\Models`, dan autoload PSR-4 Composer membedakan huruf besar/kecil.
   Di macOS (XAMPP) tidak terasa, tetapi di Linux/hosting semua halaman error
   `Class "App\Models\User" not found`.
2. **Migrasi bisa jalan di SQLite.** `ALTER TABLE ... ADD CONSTRAINT CHECK` hanya
   didukung MySQL/MariaDB, sehingga `php artisan test` (memakai SQLite) langsung gagal.
   CHECK constraint sekarang dilewati khusus untuk SQLite; di MySQL tetap dipasang.
3. **Fitur Log Aktivitas yang belum tersambung.** `LogController`, `LogAktivitas`, dan
   view `log/index` sudah ada tetapi tidak punya route, tidak ada di menu, dan tidak
   ada satu pun aksi yang dicatat.

## Fitur baru / peningkatan
- **Log Aktivitas** (menu *Lainnya → Log Aktivitas*, khusus staff): mencatat login,
  login gagal, logout, registrasi, CRUD layanan/promo/karyawan/pelanggan,
  transaksi kios, isi berat, ubah status, pesanan pelanggan, ulasan, ubah profil, dan export.
- **Export CSV transaksi** (tombol di halaman Transaksi). Mengikuti filter pencarian/status
  yang sedang aktif, bisa langsung dibuka di Excel (pemisah `;`, UTF-8 BOM).
- **Pembatasan percobaan login** (maks. 5 kali per menit) untuk mencegah brute force,
  plus halaman error **429** dengan desain yang sama dengan halaman error lain.
- **Validasi promo**: promo baru tidak boleh bertanggal berlaku di masa lalu.

## Automated testing
Contoh test bawaan Laravel (yang salah satunya gagal) diganti dengan **28 test / 138 assertion**:

| File | Yang diuji |
|---|---|
| `tests/Feature/AuthTest.php` | login, login gagal, rate limit, registrasi selalu customer + password di-hash, validasi username, logout |
| `tests/Feature/HakAksesTest.php` | tamu diarahkan ke login, pelanggan tidak bisa membuka halaman staff, nota milik orang lain tidak bisa dibuka |
| `tests/Feature/AlurPesananTest.php` | alur lengkap pesan → timbang → status → ulasan, perhitungan diskon promo, pembatalan, promo kedaluwarsa |
| `tests/Feature/FiturAdminTest.php` | CRUD layanan, validasi promo, transaksi kios, export CSV, log aktivitas |
| `tests/Unit/HelperTest.php` | `rupiah()`, `formatBerat()`, `usernameValid()`, `normalUser()` |

Menjalankan test:
```bash
php artisan test
```
