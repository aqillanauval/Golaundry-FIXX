<?php
// ============================================================
// config.php
// Wajib dipanggil PALING ATAS di setiap halaman (require_once).
// Isinya: mulai session, zona waktu, lokasi folder, dan fungsi bantu.
// ============================================================
session_start(); // wajib sebelum ada output apa pun, karena PHP mengirim cookie session di sini

date_default_timezone_set('Asia/Jakarta');

// lokasi folder data (file .txt) dan folder upload foto promo
define('FOLDER_DATA', __DIR__ . '/../data/');
define('FOLDER_UPLOAD', __DIR__ . '/../uploads/promo/');

// urutan status pesanan laundry:
// Menunggu Jemput = pelanggan sudah pesan lewat aplikasi, cucian belum diambil & belum ditimbang
// Diterima        = cucian sudah diambil / diantar ke kios dan sudah ditimbang
$daftarStatus = ['Menunggu Jemput', 'Diterima', 'Dicuci', 'Selesai', 'Diambil'];

// status setelah cucian ditimbang (dipakai admin untuk mengubah progres)
$statusProses = array_slice($daftarStatus, 1);

require_once __DIR__ . '/fungsi.php';

siapkanData(); // bikin folder & file .txt kalau belum ada
