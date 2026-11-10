<?php
// views/promo/index.php  -  Variabel: $error, $form, $modeEdit, $daftar
require APP_PATH . '/views/layout/header.php';
$tampilAksi = true; // dipakai kartu_promo.php
?>

<div class="dua-kolom">
  <section class="card">
    <h3><?= $modeEdit ? 'Edit Promo' : 'Tambah Promo' ?></h3>

    <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <!-- enctype="multipart/form-data" wajib supaya file foto ikut terkirim -->
    <form method="post" action="promo.php" class="form" enctype="multipart/form-data">
      <?= csrfField() ?>
      <input type="hidden" name="aksi" value="simpan">
      <input type="hidden" name="id" value="<?= e($form['id']) ?>">

      <div class="field">
        <label for="judul">Judul Promo</label>
        <input type="text" id="judul" name="judul" maxlength="100" placeholder="Contoh: Diskon Mahasiswa" value="<?= e($form['judul']) ?>" required>
      </div>
      <div class="field">
        <label for="deskripsi">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" rows="3" maxlength="500" placeholder="Jelaskan syarat dan keuntungan promo" required><?= e($form['deskripsi']) ?></textarea>
      </div>
      <div class="form-grid">
        <div class="field">
          <label for="diskon">Diskon (%)</label>
          <input type="number" id="diskon" name="diskon" min="1" max="100" value="<?= $form['diskon'] ? e($form['diskon']) : '' ?>" required>
        </div>
        <div class="field">
          <label for="sampai">Berlaku sampai</label>
          <input type="date" id="sampai" name="sampai" value="<?= e($form['berlaku_sampai']) ?>" required>
        </div>
      </div>
      <div class="field">
        <label for="foto">Foto Banner (JPG/PNG, maks 2 MB)</label>
        <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png">
        <div class="preview-foto" id="previewFoto" <?= ($form['foto'] !== '' && PromoModel::adaFoto($form)) ? '' : 'hidden' ?>>
          <img id="previewImg" alt="Pratinjau foto"
               src="<?= ($form['foto'] !== '') ? 'uploads/promo/' . e($form['foto']) : '' ?>">
          <small>Pratinjau foto<?= $modeEdit ? ' (kosongkan pilihan file kalau tidak ingin mengganti foto)' : '' ?></small>
        </div>
      </div>

      <div class="form-aksi">
        <button type="submit" class="btn btn-primary"><?= $modeEdit ? 'Simpan Perubahan' : 'Tambah Promo' ?></button>
        <?php if ($modeEdit): ?><a href="promo.php" class="btn btn-ghost">Batal</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section>
    <div class="judul-seksi"><h3>Daftar Promo <span class="jumlah"><?= count($daftar) ?></span></h3></div>
    <?php if (count($daftar) === 0): ?>
      <div class="card kosong-besar"><?= ikon('gift', 40) ?><p>Belum ada promo. Yuk buat promo pertamamu!</p></div>
    <?php endif; ?>
    <div class="grid-promo">
      <?php foreach ($daftar as $p): include APP_PATH . '/views/partials/kartu_promo.php'; endforeach; ?>
    </div>
  </section>
</div>

<?php require APP_PATH . '/views/layout/footer.php'; ?>
