-- =========================================================
-- GOLaundry (Laravel) - struktur database MariaDB
-- CADANGAN: cara utama tetap "php artisan migrate --seed".
-- Pakai file ini hanya kalau migration bermasalah:
-- phpMyAdmin > buat database golaundry_laravel > tab Import.
-- Isinya sama dengan hasil semua file di database/migrations.
-- =========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'customer',
  `karyawan_id` bigint unsigned NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_karyawan_id_unique` (`karyawan_id`),
  CONSTRAINT `users_karyawan_id_foreign` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`id`),
  CONSTRAINT `chk_users_role` CHECK (`role` IN ('admin', 'customer')),
  CONSTRAINT `chk_users_karyawan` CHECK ((`role` = 'admin' AND `karyawan_id` IS NOT NULL) OR (`role` = 'customer' AND `karyawan_id` IS NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `layanan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nama` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `satuan` varchar(20) NOT NULL,
  CONSTRAINT `chk_layanan_harga` CHECK (`harga` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `karyawan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nama` varchar(100) NOT NULL,
  `jabatan` varchar(50) NOT NULL,
  `no_hp` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `promo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `judul` varchar(100) NOT NULL,
  `deskripsi` varchar(500) NOT NULL,
  `diskon` smallint NOT NULL,
  `berlaku_sampai` date NOT NULL,
  `foto` varchar(255) NOT NULL DEFAULT '',
  CONSTRAINT `chk_promo_diskon` CHECK (`diskon` BETWEEN 1 AND 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pesanan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `kode` varchar(20) NOT NULL,
  `user_id` bigint unsigned NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `sumber` varchar(10) NOT NULL,
  `layanan_id` bigint unsigned NULL,
  `nama_layanan` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `berat` decimal(6,1) NOT NULL DEFAULT 0.0,
  `catatan` varchar(500) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'Menunggu Jemput',
  `kurir_id` bigint unsigned NULL,
  `promo_id` bigint unsigned NULL,
  `nama_promo` varchar(100) NOT NULL DEFAULT '',
  `diskon` smallint NOT NULL DEFAULT 0,
  `tanggal` datetime NOT NULL,
  UNIQUE KEY `pesanan_kode_unique` (`kode`),
  KEY `pesanan_status_index` (`status`),
  KEY `pesanan_tanggal_index` (`tanggal`),
  CONSTRAINT `pesanan_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pesanan_layanan_id_foreign` FOREIGN KEY (`layanan_id`) REFERENCES `layanan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pesanan_promo_id_foreign` FOREIGN KEY (`promo_id`) REFERENCES `promo` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pesanan_kurir_id_foreign` FOREIGN KEY (`kurir_id`) REFERENCES `karyawan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_pesanan_sumber` CHECK (`sumber` IN ('aplikasi', 'kios')),
  CONSTRAINT `chk_pesanan_harga` CHECK (`harga` >= 0),
  CONSTRAINT `chk_pesanan_berat` CHECK (`berat` >= 0),
  CONSTRAINT `chk_pesanan_diskon` CHECK (`diskon` BETWEEN 0 AND 100),
  CONSTRAINT `chk_pesanan_status` CHECK (`status` IN ('Menunggu Jemput', 'Diterima', 'Dicuci', 'Selesai', 'Diambil'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ulasan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `pesanan_id` bigint unsigned NOT NULL,
  `rating` smallint NOT NULL,
  `komentar` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `ulasan_pesanan_id_unique` (`pesanan_id`),
  CONSTRAINT `ulasan_pesanan_id_foreign` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_ulasan_rating` CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Supaya "php artisan migrate" tahu tabel-tabel ini sudah dibuat
INSERT INTO `migrations` (`migration`, `batch`) VALUES
  ('2026_11_20_000001_create_users_table', 1),
  ('2026_11_20_000002_create_layanan_table', 1),
  ('2026_11_20_000003_create_karyawan_table', 1),
  ('2026_11_20_000004_create_promo_table', 1),
  ('2026_11_20_000005_create_pesanan_table', 1),
  ('2026_11_20_000006_create_ulasan_table', 1),
  ('2026_11_22_000007_add_kurir_id_to_pesanan_table', 1),
  ('2026_11_22_000008_add_karyawan_id_to_users_table', 1);

-- Karyawan dimasukkan lebih dulu karena akun staff menunjuk ke karyawan (id 1)
INSERT INTO `karyawan` (`nama`, `jabatan`, `no_hp`) VALUES
  ('Admin GoLaundry', 'Admin Toko', '081234560000'),
  ('Rudi Hartono', 'Kurir', '081234560001'), ('Sari Wulandari', 'Kasir', '081234560002'),
  ('Joko Susilo', 'Operator Cuci', '081234560003'), ('Andi Wijaya', 'Kurir', '081234560005');

-- Data awal (password: admin123 dan pelanggan123, sudah di-hash)
INSERT INTO `users` (`username`, `password`, `nama`, `role`, `karyawan_id`) VALUES
  ('admin@golaundry.com',     '$2y$10$RJecGaePKNPLTeIcFjmkCeUq2YZT6G3zVi8rxrmP2zkxzM2wkzgjm', 'Admin GoLaundry',     'admin',    1),
  ('pelanggan@golaundry.com', '$2y$10$1vDYJu6UKrQcheuX0GHE8eks/bR0ZZqQjwtMsRjmOfOFOR/hnB1a.', 'Pelanggan GoLaundry', 'customer', NULL);

INSERT INTO `layanan` (`nama`, `harga`, `satuan`) VALUES
  ('Cuci Kering', 7000, 'kg'), ('Cuci Setrika', 10000, 'kg'),
  ('Dry Clean', 15000, 'item'), ('Cuci Express', 15000, 'kg');

INSERT INTO `promo` (`judul`, `deskripsi`, `diskon`, `berlaku_sampai`) VALUES
  ('Diskon Mahasiswa', 'Tunjukkan KTM dan dapatkan potongan harga untuk semua layanan cuci kiloan.', 15, '2026-12-31'),
  ('Cuci Express Hemat', 'Butuh cepat? Layanan Cuci Express lebih hemat di akhir pekan.', 10, '2026-11-30');
