<?php
// views/pelanggan/index.php  -  Variabel: $error, $form, $modeEdit, $daftar (objek Pelanggan)
require FOLDER_APP . '/views/layout/header.php';
?>

<div class="dua-kolom">
  <section class="card">
    <h3><?= $modeEdit ? 'Edit Pelanggan' : 'Tambah Pelanggan' ?></h3>

    <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="pelanggan.php" class="form">
      <?= inputToken() ?>
      <input type="hidden" name="aksi" value="simpan">
      <input type="hidden" name="id" value="<?= e($form['id']) ?>">

      <div class="field">
        <label for="nama">Nama Lengkap</label>
        <input type="text" id="nama" name="nama" maxlength="100" value="<?= e($form['nama']) ?>" required>
      </div>
      <div class="field">
        <label for="username">Email atau No. HP</label>
        <input type="text" id="username" name="username" maxlength="100" value="<?= e($form['username']) ?>"
               <?= $modeEdit ? 'readonly' : '' ?> required>
      </div>
      <div class="field">
        <label for="password"><?= $modeEdit ? 'Password Baru (kosongkan jika tidak diganti)' : 'Password' ?></label>
        <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" <?= $modeEdit ? '' : 'required' ?>>
      </div>

      <div class="form-aksi">
        <button type="submit" class="btn btn-primary"><?= $modeEdit ? 'Simpan Perubahan' : 'Tambah Pelanggan' ?></button>
        <?php if ($modeEdit): ?><a href="pelanggan.php" class="btn btn-ghost">Batal</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section class="card">
    <div class="card-kepala">
      <h3>Daftar Pelanggan <span class="jumlah"><?= count($daftar) ?></span></h3>
      <input type="search" class="cari" placeholder="Cari pelanggan..." data-cari="tabelPelanggan">
    </div>
    <div class="tabel-wrap">
      <table id="tabelPelanggan">
        <thead><tr><th>Nama</th><th>Email / No. HP</th><th class="kanan">Aksi</th></tr></thead>
        <tbody>
          <?php if (count($daftar) === 0): ?>
            <tr data-kosong><td colspan="3" class="kosong">Belum ada pelanggan.</td></tr>
          <?php endif; ?>
          <?php foreach ($daftar as $p): ?>
            <tr>
              <td><strong><?= e($p->getNama()) ?></strong></td>
              <td><?= e($p->getUsername()) ?></td>
              <td class="aksi">
                <a href="pelanggan.php?edit=<?= $p->getId() ?>" class="btn btn-sm btn-ghost">Edit</a>
                <?= formHapus($p->getId(), 'Yakin hapus akun ' . $p->getNama() . '? Pesanan yang belum dijemput ikut dibatalkan.') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php require FOLDER_APP . '/views/layout/footer.php'; ?>
