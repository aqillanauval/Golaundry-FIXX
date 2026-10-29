<?php

require_once 'includes/config.php';

$error = '';
$isi = ['nama' => '', 'username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = bersihkan($_POST['nama'] ?? '');
    $username = normalUser($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $ulangi = $_POST['ulangi'] ?? '';

    $isi['nama'] = $nama;
    $isi['username'] = $username;

        if ($nama === '' || $username === '' || $password === '' || $ulangi === '') {
        $error = 'Semua kolom wajib diisi ya.';
    } elseif (!usernameValid($username)) {
        $error = 'Isi dengan email yang valid atau nomor HP (9-15 angka).';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $ulangi) {
        $error = 'Ulangi password belum sama.';
    } elseif (cariUser($username) !== false) {
        $error = 'Email/No. HP ini sudah terdaftar.';
    } elseif (!tambahUser($username, $password, $nama, 'customer')) {
        $error = 'Email/No. HP ini sudah terdaftar.';
    } else {
        setFlash('sukses', 'Akun berhasil dibuat! Silakan masuk.');
        redirect('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar - GOLaundry</title>
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

      <h1>Buat Akun Baru</h1>
      <p class="sub">Daftar untuk mulai menggunakan layanan laundry.</p>

      <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" action="register.php" id="formRegister" novalidate>

        <div class="input-group">
          <span class="ikon-kiri"><?= ikon('user', 20) ?></span>
          <input type="text" name="nama" id="nama" placeholder="Nama Lengkap"
                 value="<?= e($isi['nama']) ?>" autocomplete="name">
        </div>

        <div class="input-group">
          <span class="ikon-kiri"><?= ikon('mail', 20) ?></span>
          <input type="text" name="username" id="username" placeholder="Email atau No. HP"
                 value="<?= e($isi['username']) ?>" autocomplete="username">
        </div>

        <div class="input-group">
          <span class="ikon-kiri"><?= ikon('lock', 20) ?></span>
          <input type="password" name="password" id="password" placeholder="Password (minimal 6 karakter)"
                 autocomplete="new-password">
          <button type="button" class="ikon-kanan" data-toggle-password="password" aria-label="Lihat password">
            <span class="ikon-mata"><?= ikon('eyeoff', 20) ?></span>
            <span class="ikon-mata-buka" hidden><?= ikon('eye', 20) ?></span>
          </button>
        </div>

        <div class="input-group">
          <span class="ikon-kiri"><?= ikon('lock', 20) ?></span>
          <input type="password" name="ulangi" id="ulangi" placeholder="Ulangi Password"
                 autocomplete="new-password">
          <button type="button" class="ikon-kanan" data-toggle-password="ulangi" aria-label="Lihat password">
            <span class="ikon-mata"><?= ikon('eyeoff', 20) ?></span>
            <span class="ikon-mata-buka" hidden><?= ikon('eye', 20) ?></span>
          </button>
        </div>

        <p class="error-text" id="errorForm"></p>

        <button type="submit" class="btn-utama">Daftar <?= ikon('arrow', 20) ?></button>
      </form>

      <div class="pemisah"><span>atau</span></div>

      <button type="button" class="btn-google" data-belum-tersedia>
        <i class="fa-brands fa-google ikon-google" aria-hidden="true"></i>
        Daftar dengan Google
      </button>

      <p class="pindah-halaman">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
    </div>
  </div>

  <script src="assets/js/app.js"></script>
  <script src="assets/js/auth.js"></script>
</body>
</html>