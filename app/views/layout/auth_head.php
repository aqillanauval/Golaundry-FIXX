<?php // views/layout/auth_head.php: <head> untuk halaman login & register. Butuh $judulTab ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($judulTab) ?> - GOLaundry</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="assets/img/logo.png">
  <link rel="stylesheet" href="assets/css/auth.css">
  <script src="https://kit.fontawesome.com/b7c9988b7a.js" crossorigin="anonymous"></script>
</head>
<body class="auth-body">

  <div class="auth-card">

    <div class="auth-visual">
      <div class="visual-cadangan">
        <?= logoSvg(64) ?>
        <h2>GOLaundry</h2>
        <p>Laundry lebih mudah,<br>hidup lebih nyaman</p>
      </div>
      <img src="assets/img/login-banner.png" alt="GOLaundry Laundry Management" class="visual-gambar" data-gambar-banner>
    </div>

    <div class="auth-form">
      <div class="brand-mobile"><?= logoSvg(40) ?> <strong>GOLaundry</strong></div>
