<?php
$host = '127.0.0.1'; // pakai IP, bukan 'localhost' (di Windows 'localhost' bisa mencoba IPv6 dulu dan menggantung)
$db   = 'golaundry';
$port = 3306;
$user = 'root';
$pass = '';   // default XAMPP

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_TIMEOUT            => 5, // gagal cepat (5 detik), jangan loading tanpa akhir
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('Koneksi basis data gagal: ' . $e->getMessage());
    http_response_code(500);
    die('Koneksi basis data gagal. Pastikan MySQL di XAMPP menyala, database golaundry sudah diimpor, dan port/password di includes/koneksi.php benar.');
}

// exception yang tidak ditangkap dicatat di log, tidak ditampilkan ke pengguna
set_exception_handler(function ($e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo 'Terjadi kesalahan pada server. Silakan coba lagi.';
});

unset($host, $db, $port, $user, $pass, $dsn, $options);