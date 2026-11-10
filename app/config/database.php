<?php
// ============================================================
// app/config/database.php  -  PENGATURAN KONEKSI DATABASE
//
// PDO bisa dipakai untuk banyak DBMS. Untuk pindah DBMS cukup ganti
// 'driver' dan data koneksinya, kode model tidak perlu diubah.
//
//   'pgsql' -> Supabase (PostgreSQL di cloud)        <- dipakai sekarang
//   'mysql' -> MySQL/MariaDB lokal di XAMPP (cadangan)
//
// CARA MENGISI UNTUK SUPABASE:
//   Supabase Dashboard > tombol "Connect" (atas) > pilih "Session pooler".
//   Contoh string yang muncul:
//   postgresql://postgres.abcdefghijkl:[YOUR-PASSWORD]@aws-1-ap-southeast-1.pooler.supabase.com:5432/postgres
//                 \______ user ______/  \_ password _/ \_______________ host ________________/ \port/ \_db_/
//
// Kenapa "Session pooler"? Koneksi langsung (db.xxx.supabase.co) hanya IPv6,
// sedangkan kebanyakan jaringan rumah/kampus masih IPv4. Session pooler
// mendukung IPv4 dan mendukung prepared statement.
//
// Setiap nilai bisa juga diisi lewat environment variable (DB_HOST, DB_PASS, ...).
// Jangan unggah password asli ke GitHub publik.
// ============================================================

return [
    'driver'  => getenv('DB_DRIVER') ?: 'pgsql',

    // ----- Supabase (pgsql) -----
    'host'    => getenv('DB_HOST') ?: 'aws-1-ap-southeast-1.pooler.supabase.com',
    'port'    => getenv('DB_PORT') ?: '5432',
    'dbname'  => getenv('DB_NAME') ?: 'postgres',
    'user'    => getenv('DB_USER') ?: 'postgres.ISI_PROJECT_REF',
    'pass'    => getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'ISI_PASSWORD_DATABASE',
    'sslmode' => getenv('DB_SSLMODE') ?: 'require', // Supabase wajib SSL

    // ----- Kalau mau kembali ke XAMPP, ganti jadi seperti ini: -----
    // 'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => '3306',
    // 'dbname' => 'golaundry', 'user' => 'root', 'pass' => '',
];
