<?php
$host = 'localhost';
$db   = 'golaundry';
$port = 3306;
$user = 'root';
$pass = ''; 
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('Koneksi basis data gagal: ' . $e->getMessage());
    http_response_code(500);
    die('Koneksi basis data gagal.');
}
set_exception_handler(function ($e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo 'Terjadi kesalahan pada server. Silakan coba lagi.';
});

unset($host, $db, $port, $user, $pass, $dsn, $options);