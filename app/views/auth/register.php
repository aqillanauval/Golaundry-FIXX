<?php
// views/auth/register.php  -  Variabel: $error, $isi (nama, username)
$judulTab = 'Daftar';
require FOLDER_APP . '/views/layout/auth_head.php';
?>
      <h1>Buat Akun Baru</h1>
      <p class="sub">Daftar untuk mulai menggunakan layanan laundry.</p>

      <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" action="register.php" id="formRegister" novalidate>
        <?= inputToken() ?>
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
