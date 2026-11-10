<?php
session_start(); 

date_default_timezone_set('Asia/Jakarta');

define('FOLDER_DATA', __DIR__ . '/../data/');
define('FOLDER_UPLOAD', __DIR__ . '/../uploads/promo/');

$daftarStatus = ['Menunggu Jemput', 'Diterima', 'Dicuci', 'Selesai', 'Diambil'];

$statusProses = array_slice($daftarStatus, 1);

<<<<<<< HEAD
require_once __DIR__ . '/koneksi.php'; 
=======
require_once __DIR__ . '/koneksi.php'; // koneksi MySQL ($pdo), dipakai untuk tabel users
>>>>>>> fb86806 (hmm)
require_once __DIR__ . '/fungsi.php';

siapkanData();