<?php
// views/karyawan/index.php  -  Variabel: $error, $form, $modeEdit, $daftar
require FOLDER_APP . '/views/layout/header.php';
?>

<div class="dua-kolom">
  <section class="card">
    <h3><?= $modeEdit ? 'Edit Karyawan' : 'Tambah Karyawan' ?></h3>

    <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="karyawan.php" class="form">
      <?= inputToken() ?>
      <input type="hidden" name="aksi" value="simpan">
      <input type="hidden" name="id" value="<?= e($form['id']) ?>">

      <div class="field">
        <label for="nama">Nama Karyawan</label>
        <input type="text" id="nama" name="nama" maxlength="100" value="<?= e($form['nama']) ?>" required>
      </div>
      <div class="field">
        <label for="jabatan">Jabatan</label>
        <input type="text" id="jabatan" name="jabatan" maxlength="50" placeholder="Contoh: Kasir, Kurir" value="<?= e($form['jabatan']) ?>" required>
      </div>
      <div class="field">
        <label for="no_hp">No. HP</label>
        <input type="text" id="no_hp" name="no_hp" maxlength="20" placeholder="08123456789" value="<?= e($form['no_hp']) ?>" required>
      </div>

      <div class="form-aksi">
        <button type="submit" class="btn btn-primary"><?= $modeEdit ? 'Simpan Perubahan' : 'Tambah Karyawan' ?></button>
        <?php if ($modeEdit): ?><a href="karyawan.php" class="btn btn-ghost">Batal</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section class="card">
    <div class="card-kepala">
      <h3>Daftar Karyawan <span class="jumlah"><?= count($daftar) ?></span></h3>
      <input type="search" class="cari" placeholder="Cari karyawan..." data-cari="tabelKaryawan">
    </div>
    <div class="tabel-wrap">
      <table id="tabelKaryawan">
        <thead><tr><th>Nama</th><th>Jabatan</th><th>No. HP</th><th class="kanan">Aksi</th></tr></thead>
        <tbody>
          <?php if (count($daftar) === 0): ?>
            <tr data-kosong><td colspan="4" class="kosong">Belum ada karyawan.</td></tr>
          <?php endif; ?>
          <?php foreach ($daftar as $k): ?>
            <tr>
              <td><strong><?= e($k['nama']) ?></strong></td>
              <td><span class="chip"><?= e($k['jabatan']) ?></span></td>
              <td><?= e($k['no_hp']) ?></td>
              <td class="aksi">
                <a href="karyawan.php?edit=<?= (int)$k['id'] ?>" class="btn btn-sm btn-ghost">Edit</a>
                <?= formHapus($k['id'], 'Yakin hapus karyawan ' . $k['nama'] . '?') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php require FOLDER_APP . '/views/layout/footer.php'; ?>
