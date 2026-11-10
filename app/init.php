<?php

ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
$pakaiHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $pakaiHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

// logout otomatis kalau 30 menit nggak ada aktivitas
define('BATAS_DIAM_DETIK', 30 * 60);
if (isset($_SESSION['aktif_terakhir']) && time() - $_SESSION['aktif_terakhir'] > BATAS_DIAM_DETIK) {
    $_SESSION = [];
    session_regenerate_id(true);
}
$_SESSION['aktif_terakhir'] = time();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

date_default_timezone_set('Asia/Jakarta');

define('FOLDER_APP', __DIR__);
define('FOLDER_UPLOAD', __DIR__ . '/../uploads/promo/');
define('FOLDER_LOG', __DIR__ . '/../storage/logs/');
define('FOLDER_DATA_LAMA', __DIR__ . '/../data/');

function tanganiError($e)
{
    error_log('[GOLaundry] ' . $e->getMessage() . ' di ' . $e->getFile() . ' baris ' . $e->getLine());
    http_response_code(500);
    echo 'Waduh, ada error di server. Coba lagi ya.';
}
set_exception_handler('tanganiError');

// ---------- koneksi & fungsi bantu ----------
require_once __DIR__ . '/koneksi.php';   
require_once __DIR__ . '/fungsi.php';    
require_once __DIR__ . '/log.php';       

// ---------- MODEL (urusan database) ----------
require_once __DIR__ . '/models/Model.php';        
require_once __DIR__ . '/models/Pengguna.php';     
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
require_once __DIR__ . '/controllers/Controller.php'; 
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
