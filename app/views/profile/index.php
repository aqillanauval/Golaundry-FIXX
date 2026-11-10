<?php
// views/profile/index.php  -  Variabel: $pengguna, $error, $errorHapus, $namaTampil
require APP_PATH . '/views/layout/header.php';
?>

<section class="card profil-card">
  <div class="profil-atas">
    <div class="avatar besar"><?= e(inisial($pengguna->getNama())) ?></div>
    <div>
      <h3><?= e($pengguna->getNama()) ?></h3>
      <p class="muted"><?= e($pengguna->getUsername()) ?> &middot; <?= e($pengguna->namaRole()) ?></p>
    </div>
  </div>

  <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="profile.php" class="form">
    <?= csrfField() ?>
    <input type="hidden" name="aksi" value="simpan">
    <div class="field">
      <label for="nama">Nama Lengkap</label>
      <input type="text" id="nama" name="nama" maxlength="100" value="<?= e($namaTampil) ?>" required>
    </div>
    <div class="form-grid">
      <div class="field">
        <label>Email / No. HP</label>
        <input type="text" value="<?= e($pengguna->getUsername()) ?>" disabled>
      </div>
      <div class="field">
        <label>Peran</label>
        <input type="text" value="<?= e($pengguna->namaRole()) ?>" disabled>
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

<?php if (!$pengguna->isAdmin()): ?>
<section class="card profil-card zona-bahaya" id="hapusAkun">
  <h3><?= ikon('trash', 18) ?> Hapus akun</h3>
  <p class="muted">Akunmu akan dihapus permanen. Pesanan yang belum dijemput ikut dibatalkan, sedangkan riwayat transaksi tetap tersimpan di laundry sebagai arsip.</p>

  <?php if ($errorHapus !== ''): ?><div class="alert alert-error"><?= e($errorHapus) ?></div><?php endif; ?>

  <form method="post" action="profile.php#hapusAkun" class="form" data-konfirmasi="Yakin hapus akunmu? Tindakan ini tidak bisa dibatalkan.">
    <?= csrfField() ?>
    <input type="hidden" name="aksi" value="hapus_akun">
    <div class="field">
      <label for="konfirmasi_password">Ketik password untuk konfirmasi</label>
      <input type="password" id="konfirmasi_password" name="konfirmasi_password" autocomplete="current-password" required>
    </div>
    <div class="form-aksi">
      <button type="submit" class="btn btn-danger">Hapus akun saya</button>
    </div>
  </form>
</section>
<?php endif; ?>

<?php require APP_PATH . '/views/layout/footer.php'; ?>
