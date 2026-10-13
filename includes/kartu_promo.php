<?php
// kartu_promo.php: tampilan satu kartu promo.
// Variabel yang dibutuhkan:
//   $p            data promo
//   $tampilAksi   true  = tombol Edit/Hapus (untuk admin)
//   $bisaDipakai  true  = tombol "Pakai promo" (untuk pelanggan)
$adaFoto = ($p['foto'] !== '' && file_exists(FOLDER_UPLOAD . $p['foto']));
$berlaku = promoMasihBerlaku($p);
?>
<article class="kartu-promo <?= $berlaku ? '' : 'kadaluarsa' ?>">
  <div class="promo-foto">
    <?php if ($adaFoto): ?>
      <img src="uploads/promo/<?= e($p['foto']) ?>" alt="<?= e($p['judul']) ?>">
    <?php else: ?>
      <div class="promo-foto-kosong"><?= ikon('percent', 44) ?></div>
    <?php endif; ?>
    <span class="promo-diskon">Hemat <?= (int)$p['diskon'] ?>%</span>
  </div>
  <div class="promo-isi">
    <h4><?= e($p['judul']) ?></h4>
    <p><?= e($p['deskripsi']) ?></p>
    <small class="promo-tanggal"><?= $berlaku ? 'Berlaku sampai ' . e(tglPendek(strtotime($p['sampai']))) : 'Promo sudah berakhir' ?></small>

    <?php if (!empty($bisaDipakai) && $berlaku): ?>
      <a class="btn btn-promo btn-sm" href="order.php?promo=<?= e($p['id']) ?>">Pakai promo ini <?= ikon('arrow', 15) ?></a>
    <?php endif; ?>

    <?php if (!empty($tampilAksi)): ?>
      <div class="aksi">
        <a class="btn btn-sm btn-ghost" href="promo.php?edit=<?= e($p['id']) ?>">Edit</a>
        <?= formHapus($p['id'], 'Yakin hapus promo ini?') ?>
      </div>
    <?php endif; ?>
  </div>
</article>
