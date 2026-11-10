<?php
// ============================================================
// views/layout/header.php  -  kerangka atas halaman setelah login
// Variabel dari controller: $pengguna (objek Admin/Pelanggan), $judulHalaman, $subJudul
// Menu sidebar diambil dari $pengguna->menu()  (polimorfisme: isi menu
// Admin dan Pelanggan berbeda, tapi cara memanggilnya sama).
// ============================================================
$halamanIni = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
  <title><?= e($judulHalaman) ?> - GOLaundry</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="icon" type="image/png" href="assets/img/logo.png">
  <script src="https://kit.fontawesome.com/b7c9988b7a.js" crossorigin="anonymous"></script>
</head>
<body>
<div class="app">

  <aside class="sidebar" id="sidebar">
    <a class="brand" href="<?= e($pengguna->urlDashboard()) ?>">
      <?= logoSvg(40) ?>
      <span>GOLaundry</span>
    </a>

    <nav class="menu">
      <?php foreach ($pengguna->menu() as [$file, $label, $ikon]): ?>
        <a href="<?= e($file) ?>" class="<?= ($halamanIni === $file) ? 'aktif' : '' ?>">
          <?= ikon($ikon, 19) ?> <span><?= e($label) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="sidebar-bawah">
      <div class="user-chip">
        <div class="avatar"><?= e(inisial($pengguna->getNama())) ?></div>
        <div class="user-info">
          <strong><?= e($pengguna->getNama()) ?></strong>
          <small><?= e($pengguna->namaRole()) ?></small>
        </div>
      </div>
      <a href="logout.php" class="tombol-keluar"><?= ikon('logout', 18) ?> Keluar</a>
    </div>
  </aside>
  <div class="overlay" id="overlay"></div>

  <main class="main">
    <header class="topbar">
      <button type="button" class="menu-toggle" id="menuToggle" aria-label="Buka menu"><?= ikon('menu', 22) ?></button>
      <div class="judul">
        <h1><?= e($judulHalaman) ?></h1>
        <?php if (!empty($subJudul)): ?><p><?= e($subJudul) ?></p><?php endif; ?>
      </div>
      <div class="tanggal"><?= e(tanggalIndo()) ?></div>
    </header>

    <div class="content">
      <?= tampilFlash() ?>
