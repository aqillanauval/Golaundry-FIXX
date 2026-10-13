<?php
// ============================================================
// profile.php  -  Profil pengguna (admin & customer)
// Pengguna bisa mengubah nama dan password.
// ============================================================
require_once 'includes/config.php';
wajibLogin();

$username = $_SESSION['user'];
$users = loadUsers();

if (!isset($users[$username])) {
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
        $users[$username]['nama'] = $namaBaru;
        if ($passwordBaru !== '') {
            $users[$username]['password'] = password_hash($passwordBaru, PASSWORD_DEFAULT);
        }
        saveUsers($users);
        $_SESSION['nama'] = $namaBaru; // perbarui data di session juga
        setFlash('sukses', 'Profil berhasil diperbarui.');
        redirect('profile.php');
    }
}

$namaTampil = ($error !== '') ? ($_POST['nama'] ?? '') : $users[$username]['nama'];
$namaRole = ($users[$username]['role'] === 'admin') ? 'Admin / Staff' : 'Pelanggan';

$judulHalaman = 'Profil Saya';
$subJudul = 'Kelola data diri akunmu.';
require 'includes/header.php';
?>

<section class="card profil-card">
  <div class="profil-atas">
    <div class="avatar besar"><?= e(inisial($users[$username]['nama'])) ?></div>
    <div>
      <h3><?= e($users[$username]['nama']) ?></h3>
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
