<?php
// views/auth/login.php  -  Variabel: $error, $usernameIsi, $ingatCentang
$judulTab = 'Masuk';
require APP_PATH . '/views/layout/auth_head.php';
?>
      <h1>Selamat Datang!</h1>
      <p class="sub">Masuk ke akun Anda untuk mengakses layanan laundry.</p>

      <?= tampilFlash() ?>
      <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" action="login.php" id="formLogin" novalidate>
        <?= csrfField() ?>
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
