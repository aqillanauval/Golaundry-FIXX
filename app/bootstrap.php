<?php
// ============================================================
// bootstrap.php  -  Dipanggil PALING ATAS oleh setiap halaman.
// Isinya: session, zona waktu, lokasi folder, autoload kelas, error handler.
// ============================================================

// cookie session lebih aman: tidak bisa dibaca JavaScript, tidak dikirim dari situs lain
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

date_default_timezone_set('Asia/Jakarta');

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);
define('FOLDER_UPLOAD', BASE_PATH . '/uploads/promo/');
define('FOLDER_LOG', BASE_PATH . '/storage/logs/');
define('FOLDER_DATA_LAMA', BASE_PATH . '/data/'); // file .txt versi lama (untuk impor)

// Autoload: saat kode memakai  new LayananModel()  PHP otomatis me-require
// file app/models/LayananModel.php. Jadi tidak perlu menulis require satu per satu.
spl_autoload_register(function (string $kelas) {
    foreach (['core', 'entities', 'models', 'controllers'] as $folder) {
        $file = APP_PATH . '/' . $folder . '/' . $kelas . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once APP_PATH . '/core/helpers.php';

// exception yang tidak tertangkap: catat di log, tampilkan pesan umum saja
set_exception_handler(function (Throwable $e) {
    error_log('[GOLaundry] ' . $e->getMessage() . ' di ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo 'Terjadi kesalahan pada server. Silakan coba lagi.';
});
