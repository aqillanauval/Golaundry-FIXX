-- ============================================================
-- supabase.sql  -  Skema database GOLaundry untuk SUPABASE (PostgreSQL)
-- Cara pakai: Supabase Dashboard > SQL Editor > New query >
--             tempel seluruh isi file ini > Run.
-- Aman dijalankan ulang (IF NOT EXISTS / ON CONFLICT DO NOTHING).
--
-- Relasi:
--   users (1) ---< (n) pesanan      pesanan.user_id    -> users.id
--   layanan (1) -< (n) pesanan      pesanan.layanan_id -> layanan.id
--   promo (1) ---< (n) pesanan      pesanan.promo_id   -> promo.id
--   pesanan (1) -- (0..1) ulasan    ulasan.pesanan_id  -> pesanan.id
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
  id         BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  username   VARCHAR(100) NOT NULL UNIQUE,          -- email atau no. HP
  password   VARCHAR(255) NOT NULL,                 -- hasil password_hash()
  nama       VARCHAR(100) NOT NULL,
  role       VARCHAR(10)  NOT NULL DEFAULT 'customer'
             CHECK (role IN ('admin', 'customer')),
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS layanan (
  id     BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  nama   VARCHAR(100) NOT NULL,
  harga  INTEGER      NOT NULL CHECK (harga > 0),
  satuan VARCHAR(20)  NOT NULL
);

CREATE TABLE IF NOT EXISTS karyawan (
  id      BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  nama    VARCHAR(100) NOT NULL,
  jabatan VARCHAR(50)  NOT NULL,
  no_hp   VARCHAR(20)  NOT NULL
);

CREATE TABLE IF NOT EXISTS promo (
  id             BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  judul          VARCHAR(100) NOT NULL,
  deskripsi      VARCHAR(500) NOT NULL,
  diskon         SMALLINT     NOT NULL CHECK (diskon BETWEEN 1 AND 100),
  berlaku_sampai DATE         NOT NULL,
  foto           VARCHAR(255) NOT NULL DEFAULT ''   -- nama file di uploads/promo/
);

-- harga, nama layanan, nama promo & diskon DISALIN ke pesanan (snapshot),
-- supaya nota lama tidak ikut berubah kalau harga layanan / promo diubah admin.
CREATE TABLE IF NOT EXISTS pesanan (
  id             BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  kode           VARCHAR(20)  NOT NULL UNIQUE,       -- No. Nota, contoh ORD6ab7da28cce8f
  user_id        BIGINT       NULL REFERENCES users(id)   ON DELETE SET NULL,
  nama_pelanggan VARCHAR(100) NOT NULL,              -- nama saat pesan / nama pelanggan kios
  sumber         VARCHAR(10)  NOT NULL CHECK (sumber IN ('aplikasi', 'kios')),
  layanan_id     BIGINT       NULL REFERENCES layanan(id) ON DELETE SET NULL,
  nama_layanan   VARCHAR(100) NOT NULL,
  harga          INTEGER      NOT NULL CHECK (harga >= 0),
  satuan         VARCHAR(20)  NOT NULL,
  berat          NUMERIC(6,1) NOT NULL DEFAULT 0 CHECK (berat >= 0),   -- 0 = belum ditimbang
  catatan        VARCHAR(500) NOT NULL DEFAULT '',   -- alamat jemput / catatan
  status         VARCHAR(20)  NOT NULL DEFAULT 'Menunggu Jemput'
                 CHECK (status IN ('Menunggu Jemput', 'Diterima', 'Dicuci', 'Selesai', 'Diambil')),
  promo_id       BIGINT       NULL REFERENCES promo(id)   ON DELETE SET NULL,
  nama_promo     VARCHAR(100) NOT NULL DEFAULT '',
  diskon         SMALLINT     NOT NULL DEFAULT 0 CHECK (diskon BETWEEN 0 AND 100),
  tanggal        TIMESTAMP    NOT NULL                -- diisi PHP (zona Asia/Jakarta)
);

CREATE INDEX IF NOT EXISTS idx_pesanan_user    ON pesanan (user_id);
CREATE INDEX IF NOT EXISTS idx_pesanan_status  ON pesanan (status);
CREATE INDEX IF NOT EXISTS idx_pesanan_tanggal ON pesanan (tanggal);

CREATE TABLE IF NOT EXISTS ulasan (
  id         BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  pesanan_id BIGINT       NOT NULL UNIQUE REFERENCES pesanan(id) ON DELETE CASCADE,
  rating     SMALLINT     NOT NULL CHECK (rating BETWEEN 1 AND 5),
  komentar   VARCHAR(500) NOT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- KEAMANAN SUPABASE (WAJIB)
-- Tabel di schema "public" otomatis bisa diakses lewat API Supabase
-- (pakai anon key yang bersifat publik). Tanpa RLS, siapa pun bisa membaca
-- tabel users beserta hash password-nya.
-- RLS diaktifkan TANPA policy = API publik tidak bisa membaca apa pun.
-- PHP tetap bisa mengakses karena login sebagai role "postgres" (pemilik tabel).
-- ------------------------------------------------------------
ALTER TABLE users    ENABLE ROW LEVEL SECURITY;
ALTER TABLE layanan  ENABLE ROW LEVEL SECURITY;
ALTER TABLE karyawan ENABLE ROW LEVEL SECURITY;
ALTER TABLE promo    ENABLE ROW LEVEL SECURITY;
ALTER TABLE pesanan  ENABLE ROW LEVEL SECURITY;
ALTER TABLE ulasan   ENABLE ROW LEVEL SECURITY;

-- ------------------------------------------------------------
-- Data awal
-- Akun contoh: admin@golaundry.com / admin123
--              pelanggan@golaundry.com / pelanggan123
-- ------------------------------------------------------------
INSERT INTO users (username, password, nama, role) VALUES
  ('admin@golaundry.com',     '$2y$10$RJecGaePKNPLTeIcFjmkCeUq2YZT6G3zVi8rxrmP2zkxzM2wkzgjm', 'Admin GoLaundry',     'admin'),
  ('pelanggan@golaundry.com', '$2y$10$1vDYJu6UKrQcheuX0GHE8eks/bR0ZZqQjwtMsRjmOfOFOR/hnB1a.', 'Pelanggan GoLaundry', 'customer')
ON CONFLICT (username) DO NOTHING;

INSERT INTO layanan (nama, harga, satuan)
SELECT * FROM (VALUES
  ('Cuci Kering',  7000,  'kg'),
  ('Cuci Setrika', 10000, 'kg'),
  ('Dry Clean',    15000, 'item'),
  ('Cuci Express', 15000, 'kg')
) AS v(nama, harga, satuan)
WHERE NOT EXISTS (SELECT 1 FROM layanan);

INSERT INTO promo (judul, deskripsi, diskon, berlaku_sampai)
SELECT * FROM (VALUES
  ('Diskon Mahasiswa',   'Tunjukkan KTM dan dapatkan potongan harga untuk semua layanan cuci kiloan.', 15, DATE '2026-12-31'),
  ('Cuci Express Hemat', 'Butuh cepat? Layanan Cuci Express lebih hemat di akhir pekan.',             10, DATE '2026-11-30')
) AS v(judul, deskripsi, diskon, berlaku_sampai)
WHERE NOT EXISTS (SELECT 1 FROM promo);
