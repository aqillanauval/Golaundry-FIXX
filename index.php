<?php
// ============================================================
// index.php
// Pintu masuk folder: http://localhost/PemrogramanWeb/
// Selalu menuju halaman LOGIN lebih dulu. Kalau masih ada sesi login
// dari sebelumnya, sesi itu dihapus dulu supaya tidak langsung masuk dashboard.
// ============================================================
require_once 'includes/config.php';

$_SESSION = [];      // kosongkan data login lama
session_destroy();   // hancurkan session di server

redirect('login.php');
