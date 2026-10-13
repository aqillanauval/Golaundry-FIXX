<?php
// ============================================================
// logout.php
// Hapus SESSION (status login). Cookie "ingat saya" sengaja dibiarkan,
// jadi email tetap terisi otomatis di halaman login kalau dicentang.
// ============================================================
session_start();
$_SESSION = [];      // kosongkan data session
session_destroy();   // hancurkan session di server

// mulai session baru yang kosong cuma untuk menampilkan pesan
session_start();
$_SESSION['flash'] = ['tipe' => 'sukses', 'pesan' => 'Kamu sudah keluar. Sampai jumpa lagi!'];

header('Location: login.php');
exit;
