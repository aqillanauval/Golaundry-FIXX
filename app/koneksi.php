<?php
// ============================================================
// koneksi.php  ->  nyambungin PHP ke database pakai PDO
// (sama kayak koneksi.php di materi "Panduan CRUD dengan PHP PDO" Langkah 2)
//
// Bedanya: database kita ada di Supabase (PostgreSQL), bukan MySQL XAMPP.
// Untungnya PDO bisa dipakai buat banyak DBMS, jadi yang beda cuma DSN-nya.
// Query di model nggak perlu diubah sama sekali.
// ============================================================

// ambil pengaturan ($jenisDb, $host, $port, $namaDb, $user, $pass, $sslmode)
if (!file_exists(__DIR__ . '/config/database.php')) {
    die('File app/config/database.php belum ada. Copy dulu app/config/database.contoh.php jadi database.php, terus isi datanya.');
}
require __DIR__ . '/config/database.php';

// DSN = alamat database: jenis driver, host, port, nama database
if ($jenisDb == 'pgsql') {
    $dsn = "pgsql:host=$host;port=$port;dbname=$namaDb;sslmode=$sslmode";
} else {
    $dsn = "mysql:host=$host;port=$port;dbname=$namaDb;charset=utf8mb4";
}

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // error SQL jadi exception (bisa di-catch)
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // hasil query jadi array asosiatif: $row['nama']
    PDO::ATTR_EMULATE_PREPARES   => false,                  // pakai prepared statement asli dari database
    PDO::ATTR_TIMEOUT            => 10,                     // kalau 10 detik nggak nyambung, langsung gagal
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // detail error (termasuk user & password) cuma dicatat di log server, JANGAN ditampilin ke user
    error_log('Koneksi database gagal: ' . $e->getMessage());
    die('Koneksi database gagal. Cek lagi isi app/config/database.php dan pastikan extension pdo_' . $jenisDb . ' sudah aktif di php.ini.');
}

// password udah nggak dipakai lagi, hapus dari memori
unset($pass);
