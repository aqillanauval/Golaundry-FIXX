# GOLaundry (Laravel)

Aplikasi laundry antar-jemput untuk mahasiswa dan anak kos. Dibuat dengan Laravel + MariaDB (XAMPP).

Cara memasang, struktur folder, hubungan dengan materi kuliah, dan daftar uji rubrik ada di **[PANDUAN.md](PANDUAN.md)**.

Ringkasnya:

```
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan migrate --seed
```
