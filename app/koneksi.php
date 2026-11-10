<?php

if (!file_exists(__DIR__ . '/config/database.php')) {
    die('File app/config/database.php belum ada. Copy dulu app/config/database.contoh.php jadi database.php, terus isi datanya.');
}
require __DIR__ . '/config/database.php';

if ($jenisDb == 'pgsql') {
    $dsn = "pgsql:host=$host;port=$port;dbname=$namaDb;sslmode=$sslmode";
} else {
    $dsn = "mysql:host=$host;port=$port;dbname=$namaDb;charset=utf8mb4";
}

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   
    PDO::ATTR_EMULATE_PREPARES   => false,                 
    PDO::ATTR_TIMEOUT            => 10,                   
    PDO::ATTR_PERSISTENT         => true,                 
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('Koneksi database gagal: ' . $e->getMessage());
    die('Koneksi database gagal. Cek lagi isi app/config/database.php dan pastikan extension pdo_' . $jenisDb . ' sudah aktif di php.ini.');
}

unset($pass);