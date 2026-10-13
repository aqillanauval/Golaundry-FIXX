<?php
// ============================================================
// login.php  (HTML + PHP jadi satu)
// STATE yang dipakai:
//   - SESSION : menyimpan status login ($_SESSION['user'], 'nama', 'role')
//   - COOKIE  : 'ingat_user' (fitur "Ingat saya", berlaku 30 hari)
// Role (admin / customer) otomatis dibaca dari data akun, jadi
// tidak perlu dipilih lagi di form.
// ============================================================
require_once 'includes/config.php';

$error = '';
$usernameIsi = isset($_COOKIE['ingat_user']) ? $_COOKIE['ingat_user'] : '';
$ingatCentang = isset($_COOKIE['ingat_user']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = normalUser($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $ingat = isset($_POST['ingat']);

    $usernameIsi = $username;
    $ingatCentang = $ingat;

    if ($username === '' || $password === '') {
        $error = 'Email/No. HP dan password wajib diisi ya.';
    } else {
        $users = loadUsers();

        if (isset($users[$username]) && cekPassword($password, $users[$username]['password'])) {
            // login berhasil -> simpan statusnya di SESSION
            session_regenerate_id(true); // ganti ID session biar lebih aman
            $_SESSION['user'] = $username;
            $_SESSION['nama'] = $users[$username]['nama'];
            $_SESSION['role'] = $users[$username]['role'];

            // akun lama masih berisi password polos -> ubah jadi hash
            if (substr($users[$username]['password'], 0, 4) !== '$2y$') {
                $users[$username]['password'] = password_hash($password, PASSWORD_DEFAULT);
                saveUsers($users);
            }

            // "Ingat saya" -> simpan username di COOKIE selama 30 hari
            if ($ingat) {
                setcookie('ingat_user', $username, time() + (60 * 60 * 24 * 30), '/');
            } else {
                setcookie('ingat_user', '', time() - 3600, '/'); // hapus cookie
            }

            redirect(urlDashboard($_SESSION['role']));
        } else {
            $error = 'Email/No. HP atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk - GOLaundry</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="assets/img/logo.png">
  <link rel="stylesheet" href="assets/css/auth.css">
  <script src="https://kit.fontawesome.com/b7c9988b7a.js" crossorigin="anonymous"></script>
</head>
<body class="auth-body">

  <div class="auth-card">

    <!-- Sisi kiri: gambar. Taruh gambarmu di assets/img/login-banner.png -->
    <div class="auth-visual">
      <div class="visual-cadangan">
        <?= logoSvg(64) ?>
        <h2>GOLaundry</h2>
        <p>Laundry lebih mudah,<br>hidup lebih nyaman</p>
      </div>
      <img src="assets/img/login-banner.png" alt="GOLaundry Laundry Management" class="visual-gambar" data-gambar-banner>
    </div>

    <!-- Sisi kanan: form -->
    <div class="auth-form">
      <div class="brand-mobile"><?= logoSvg(40) ?> <strong>GOLaundry</strong></div>

      <h1>Selamat Datang!</h1>
      <p class="sub">Masuk ke akun Anda untuk mengakses layanan laundry.</p>

      <?= tampilFlash() ?>
      <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" action="login.php" id="formLogin" novalidate>

        <div class="input-group">
          <span class="ikon-kiri"><?= ikon('mail', 20) ?></span>
          <input type="text" name="username" id="username" placeholder="Email atau No. HP"
                 value="<?= e($usernameIsi) ?>" autocomplete="username">
        </div>

        <div class="input-group">
          <span class="ikon-kiri"><?= ikon('lock', 20) ?></span>
          <input type="password" name="password" id="password" placeholder="Password"
                 autocomplete="current-password">
          <button type="button" class="ikon-kanan" data-toggle-password="password" aria-label="Lihat password">
            <span class="ikon-mata"><?= ikon('eyeoff', 20) ?></span>
            <span class="ikon-mata-buka" hidden><?= ikon('eye', 20) ?></span>
          </button>
        </div>

        <p class="error-text" id="errorForm"></p>

        <div class="baris-antara">
          <label class="centang">
            <input type="checkbox" name="ingat" <?= $ingatCentang ? 'checked' : '' ?>>
            <span>Ingat saya</span>
          </label>
          <a href="#" data-belum-tersedia>Lupa password?</a>
        </div>

        <button type="submit" class="btn-utama">Masuk <?= ikon('arrow', 20) ?></button>
      </form>

      <div class="pemisah"><span>atau</span></div>

      <button type="button" class="btn-google" data-belum-tersedia>
        <i class="fa-brands fa-google ikon-google" aria-hidden="true"></i>
        Masuk dengan Google
      </button>

      <p class="pindah-halaman">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </div>
  </div>

  <script src="assets/js/app.js"></script>
  <script src="assets/js/auth.js"></script>
</body>
</html>
