<?php

require_once 'includes/config.php';
wajibLogin();

$username = $_SESSION['user'];
$akun = cariUser($username);

if ($akun === false) {
    redirect('logout.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaBaru = bersihkan($_POST['nama'] ?? '');
    $passwordBaru = $_POST['password_baru'] ?? '';

    if ($namaBaru === '') {
        $error = 'Nama tidak boleh kosong.';
    } elseif ($passwordBaru !== '' && strlen($passwordBaru) < 6) {
        $error = 'Password baru minimal 6 karakter.';
    } else {
        ubahUser($username, $namaBaru, $passwordBaru);
        $_SESSION['nama'] = $namaBaru;
        setFlash('sukses', 'Profil berhasil diperbarui.');
        redirect('profile.php');
    }
}

$namaTampil = ($error !== '') ? ($_POST['nama'] ?? '') : $akun['nama'];
$namaRole = ($akun['role'] === 'admin') ? 'Admin / Staff' : 'Pelanggan';

$judulHalaman = 'Profil Saya';
$subJudul = 'Kelola data diri akunmu.';
require 'includes/header.php';
?>

<section class="card profil-card">
  <div class="profil-atas">
    <div class="avatar besar"><?= e(inisial($akun['nama'])) ?></div>
    <div>
      <h3><?= e($akun['nama']) ?></h3>
      <p class="muted"><?= e($username) ?> &middot; <?= e($namaRole) ?></p>
    </div>
  </div>

  <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="profile.php" class="form">
    <div class="field">
      <label for="nama">Nama Lengkap</label>
      <input type="text" id="nama" name="nama" value="<?= e($namaTampil) ?>" required>
    </div>
    <div class="form-grid">
      <div class="field">
        <label>Email / No. HP</label>
        <input type="text" value="<?= e($username) ?>" disabled>
      </div>
      <div class="field">
        <label>Peran</label>
        <input type="text" value="<?= e($namaRole) ?>" disabled>
      </div>
    </div>
    <div class="field">
      <label for="password_baru">Password baru</label>
      <input type="password" id="password_baru" name="password_baru" placeholder="Kosongkan jika tidak ingin mengganti" autocomplete="new-password">
      <small class="bantuan">Minimal 6 karakter.</small>
    </div>
    <div class="form-aksi">
      <button type="submit" class="btn btn-primary">Simpan perubahan</button>
    </div>
  </form>
</section>

<?php require 'includes/footer.php'; ?>