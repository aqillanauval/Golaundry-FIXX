<?php
// ============================================================
// init.php  ->  dipanggil PALING ATAS di setiap halaman
// Isinya: nyalain session, set zona waktu, terus panggil semua file
// yang dibutuhin (koneksi, fungsi, model, controller).
// ============================================================

ini_set('session.cookie_httponly', 1); // cookie session nggak bisa dibaca JavaScript (lebih aman)
session_start();

date_default_timezone_set('Asia/Jakarta');

// lokasi folder penting
define('FOLDER_APP', __DIR__);
define('FOLDER_UPLOAD', __DIR__ . '/../uploads/promo/');
define('FOLDER_LOG', __DIR__ . '/../storage/logs/');
define('FOLDER_DATA_LAMA', __DIR__ . '/../data/'); // file .txt versi lama (buat fitur impor)

// kalau ada error yang lupa di-catch: catat di log, tampilkan pesan umum aja
function tanganiError($e)
{
    error_log('[GOLaundry] ' . $e->getMessage() . ' di ' . $e->getFile() . ' baris ' . $e->getLine());
    http_response_code(500);
    echo 'Waduh, ada error di server. Coba lagi ya.';
}
set_exception_handler('tanganiError');

// ---------- koneksi & fungsi bantu ----------
require_once __DIR__ . '/koneksi.php';   // bikin variabel $pdo
require_once __DIR__ . '/fungsi.php';    // fungsi kecil: e(), rupiah(), ikon(), token form, dll
require_once __DIR__ . '/log.php';       // catat aktivitas ke file (Modul 9)

// ---------- MODEL (urusan database) ----------
require_once __DIR__ . '/models/Model.php';        // class induk
require_once __DIR__ . '/models/Pengguna.php';     // class induk Admin & Pelanggan
require_once __DIR__ . '/models/Admin.php';
require_once __DIR__ . '/models/Pelanggan.php';
require_once __DIR__ . '/models/Pesanan.php';
require_once __DIR__ . '/models/UserModel.php';
require_once __DIR__ . '/models/LayananModel.php';
require_once __DIR__ . '/models/KaryawanModel.php';
require_once __DIR__ . '/models/PromoModel.php';
require_once __DIR__ . '/models/PesananModel.php';
require_once __DIR__ . '/models/UlasanModel.php';
require_once __DIR__ . '/models/ImporModel.php';

// ---------- CONTROLLER (ngatur alur halaman) ----------
require_once __DIR__ . '/controllers/Controller.php'; // class induk
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardAdminController.php';
require_once __DIR__ . '/controllers/DashboardCustomerController.php';
require_once __DIR__ . '/controllers/LayananController.php';
require_once __DIR__ . '/controllers/KaryawanController.php';
require_once __DIR__ . '/controllers/PelangganController.php';
require_once __DIR__ . '/controllers/PromoController.php';
require_once __DIR__ . '/controllers/TransaksiController.php';
require_once __DIR__ . '/controllers/OrderController.php';
require_once __DIR__ . '/controllers/RiwayatController.php';
require_once __DIR__ . '/controllers/NotaController.php';
require_once __DIR__ . '/controllers/ProfileController.php';
require_once __DIR__ . '/controllers/LogController.php';
require_once __DIR__ . '/controllers/ImporController.php';
