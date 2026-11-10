-- ============================================================
-- mysql.sql  -  Skema yang SAMA untuk MySQL/MariaDB di XAMPP (cadangan)
-- Dipakai kalau DB_DRIVER di app/config/database.php = 'mysql'.
-- phpMyAdmin > tab SQL > tempel > Go   (atau menu Import).
-- ============================================================
CREATE DATABASE IF NOT EXISTS golaundry CHARACTER SET utf8mb4;
USE golaundry;

CREATE TABLE IF NOT EXISTS users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  username   VARCHAR(100) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  nama       VARCHAR(100) NOT NULL,
  role       VARCHAR(10)  NOT NULL DEFAULT 'customer' CHECK (role IN ('admin', 'customer')),
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS layanan (
  id     INT AUTO_INCREMENT PRIMARY KEY,
  nama   VARCHAR(100) NOT NULL,
  harga  INT          NOT NULL CHECK (harga > 0),
  satuan VARCHAR(20)  NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS karyawan (
  id      INT AUTO_INCREMENT PRIMARY KEY,
  nama    VARCHAR(100) NOT NULL,
  jabatan VARCHAR(50)  NOT NULL,
  no_hp   VARCHAR(20)  NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS promo (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  judul          VARCHAR(100) NOT NULL,
  deskripsi      VARCHAR(500) NOT NULL,
  diskon         SMALLINT     NOT NULL CHECK (diskon BETWEEN 1 AND 100),
  berlaku_sampai DATE         NOT NULL,
  foto           VARCHAR(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pesanan (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  kode           VARCHAR(20)  NOT NULL UNIQUE,
  user_id        INT          NULL,
  nama_pelanggan VARCHAR(100) NOT NULL,
  sumber         VARCHAR(10)  NOT NULL CHECK (sumber IN ('aplikasi', 'kios')),
  layanan_id     INT          NULL,
  nama_layanan   VARCHAR(100) NOT NULL,
  harga          INT          NOT NULL CHECK (harga >= 0),
  satuan         VARCHAR(20)  NOT NULL,
  berat          DECIMAL(6,1) NOT NULL DEFAULT 0 CHECK (berat >= 0),
  catatan        VARCHAR(500) NOT NULL DEFAULT '',
  status         VARCHAR(20)  NOT NULL DEFAULT 'Menunggu Jemput'
                 CHECK (status IN ('Menunggu Jemput', 'Diterima', 'Dicuci', 'Selesai', 'Diambil')),
  promo_id       INT          NULL,
  nama_promo     VARCHAR(100) NOT NULL DEFAULT '',
  diskon         SMALLINT     NOT NULL DEFAULT 0 CHECK (diskon BETWEEN 0 AND 100),
  tanggal        DATETIME     NOT NULL,
  INDEX idx_pesanan_status (status),
  INDEX idx_pesanan_tanggal (tanggal),
  CONSTRAINT fk_pesanan_user    FOREIGN KEY (user_id)    REFERENCES users(id)   ON DELETE SET NULL,
  CONSTRAINT fk_pesanan_layanan FOREIGN KEY (layanan_id) REFERENCES layanan(id) ON DELETE SET NULL,
  CONSTRAINT fk_pesanan_promo   FOREIGN KEY (promo_id)   REFERENCES promo(id)   ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ulasan (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  pesanan_id INT          NOT NULL UNIQUE,
  rating     SMALLINT     NOT NULL CHECK (rating BETWEEN 1 AND 5),
  komentar   VARCHAR(500) NOT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ulasan_pesanan FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Akun contoh: admin@golaundry.com / admin123, pelanggan@golaundry.com / pelanggan123
INSERT IGNORE INTO users (username, password, nama, role) VALUES
  ('admin@golaundry.com',     '$2y$10$RJecGaePKNPLTeIcFjmkCeUq2YZT6G3zVi8rxrmP2zkxzM2wkzgjm', 'Admin GoLaundry',     'admin'),
  ('pelanggan@golaundry.com', '$2y$10$1vDYJu6UKrQcheuX0GHE8eks/bR0ZZqQjwtMsRjmOfOFOR/hnB1a.', 'Pelanggan GoLaundry', 'customer');

INSERT INTO layanan (nama, harga, satuan)
SELECT * FROM (
  SELECT 'Cuci Kering' AS nama, 7000 AS harga, 'kg' AS satuan
  UNION ALL SELECT 'Cuci Setrika', 10000, 'kg'
  UNION ALL SELECT 'Dry Clean', 15000, 'item'
  UNION ALL SELECT 'Cuci Express', 15000, 'kg'
) AS v WHERE NOT EXISTS (SELECT 1 FROM layanan);

INSERT INTO promo (judul, deskripsi, diskon, berlaku_sampai)
SELECT * FROM (
  SELECT 'Diskon Mahasiswa' AS judul, 'Tunjukkan KTM dan dapatkan potongan harga untuk semua layanan cuci kiloan.' AS deskripsi, 15 AS diskon, '2026-12-31' AS berlaku_sampai
  UNION ALL SELECT 'Cuci Express Hemat', 'Butuh cepat? Layanan Cuci Express lebih hemat di akhir pekan.', 10, '2026-11-30'
) AS v WHERE NOT EXISTS (SELECT 1 FROM promo);
