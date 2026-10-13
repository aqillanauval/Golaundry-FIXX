<?php
// ============================================================
// header.php
// Kerangka atas halaman setelah login: sidebar + topbar.
// Sebelum di-include, halaman mengisi dulu:
//   $judulHalaman  (wajib)   dan   $subJudul (opsional)
// ============================================================
$halamanIni = basename($_SERVER['SCRIPT_NAME']);

if ($_SESSION['role'] === 'admin') {
    $menu = [
        ['dashboard-admin.php', 'Dashboard', 'home'],
        ['transaksi.php',       'Transaksi', 'file'],
        ['layanan.php',         'Layanan',   'tag'],
        ['promo.php',           'Promo',     'gift'],
        ['pelanggan.php',       'Pelanggan', 'users'],
        ['karyawan.php',        'Karyawan',  'briefcase'],
        ['profile.php',         'Profil',    'user'],
    ];
    $namaRole = 'Admin / Staff';
} else {
    $menu = [
        ['dashboard-customer.php', 'Dashboard',    'home'],
        ['order.php',              'Buat Pesanan', 'plus'],
        ['riwayat.php',            'Riwayat',      'clock'],
        ['profile.php',            'Profil',       'user'],
    ];
    $namaRole = 'Pelanggan';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <a class="brand" href="<?= e(urlDashboard($_SESSION['role'])) ?>">
      <?= logoSvg(40) ?>
      <span>GOLaundry</span>
    </a>

    <nav class="menu">
      <?php foreach ($menu as $m): ?>
        <a href="<?= e($m[0]) ?>" class="<?= ($halamanIni === $m[0]) ? 'aktif' : '' ?>">
          <?= ikon($m[2], 19) ?> <span><?= e($m[1]) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="sidebar-bawah">
      <div class="user-chip">
        <div class="avatar"><?= e(inisial($_SESSION['nama'])) ?></div>
        <div class="user-info">
          <strong><?= e($_SESSION['nama']) ?></strong>
          <small><?= e($namaRole) ?></small>
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
