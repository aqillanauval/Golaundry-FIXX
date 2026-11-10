<?php
// views/riwayat/index.php  -  Variabel: $error, $formUlasan, $pesananSaya (Pesanan[])
require APP_PATH . '/views/layout/header.php';
?>

<?php if ($formUlasan !== null): ?>
<section class="card sorot" id="formUlasan">
  <h3>Ulasan untuk Pesanan <?= e($formUlasan['id']) ?></h3>

  <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="riwayat.php" class="form">
    <?= csrfField() ?>
    <input type="hidden" name="aksi" value="simpan_ulasan">
    <input type="hidden" name="id" value="<?= e($formUlasan['id']) ?>">

    <div class="field">
      <label>Rating</label>
      <div class="bintang-input">
        <?php for ($b = 5; $b >= 1; $b--): ?>
          <input type="radio" name="rating" id="bintang<?= $b ?>" value="<?= $b ?>" <?= (int)$formUlasan['rating'] === $b ? 'checked' : '' ?>>
          <label for="bintang<?= $b ?>" title="<?= $b ?> bintang">&#9733;</label>
        <?php endfor; ?>
      </div>
    </div>
    <div class="field">
      <label for="komentar">Komentar</label>
      <textarea id="komentar" name="komentar" rows="3" maxlength="500" placeholder="Ceritakan pengalamanmu" required><?= e($formUlasan['komentar']) ?></textarea>
    </div>

    <div class="form-aksi">
      <button type="submit" class="btn btn-primary">Simpan Ulasan</button>
      <a href="riwayat.php" class="btn btn-ghost">Batal</a>
    </div>
  </form>
</section>
<?php endif; ?>

<section class="card">
  <div class="card-kepala">
    <h3>Semua Pesanan <span class="jumlah"><?= count($pesananSaya) ?></span></h3>
    <input type="search" class="cari" placeholder="Cari nota atau layanan..." data-cari="tabelRiwayat">
  </div>
  <div class="tabel-wrap">
    <table id="tabelRiwayat">
      <thead><tr><th>No. Nota</th><th>Tanggal</th><th>Layanan</th><th>Berat</th><th>Total</th><th>Status</th><th>Ulasan</th></tr></thead>
      <tbody>
        <?php if (count($pesananSaya) === 0): ?>
          <tr data-kosong><td colspan="7" class="kosong">Belum ada transaksi.</td></tr>
        <?php endif; ?>
        <?php foreach ($pesananSaya as $o): ?>
          <tr>
            <td><a href="nota.php?id=<?= e($o->kode) ?>" class="kode"><?= e($o->kode) ?></a></td>
            <td><?= e($o->tanggalTampil()) ?></td>
            <td><?= e($o->nama_layanan) ?></td>
            <td><?= $o->labelBerat() ?></td>
            <td class="uang"><?= $o->labelTotal() ?></td>
            <td><?= badgeStatus($o->status) ?></td>
            <td>
              <?php if ($o->sudahSelesai()): ?>
                <?php if ($o->punyaUlasan()): $r = (int)$o->ulasan_rating; ?>
                  <div class="bintang"><?= str_repeat('&#9733;', $r) ?><span><?= str_repeat('&#9733;', 5 - $r) ?></span></div>
                  <div class="komentar"><?= e($o->ulasan_komentar) ?></div>
                  <div class="aksi">
                    <a href="riwayat.php?ulas=<?= e($o->kode) ?>" class="btn btn-sm btn-ghost">Edit</a>
                    <?= formHapus($o->kode, 'Yakin hapus ulasan ini?') ?>
                  </div>
                <?php else: ?>
                  <a href="riwayat.php?ulas=<?= e($o->kode) ?>" class="btn btn-sm btn-primary">Beri Ulasan</a>
                <?php endif; ?>
              <?php else: ?>
                <span class="muted">-</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require APP_PATH . '/views/layout/footer.php'; ?>
