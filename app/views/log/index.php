<?php
// views/log/index.php  -  Variabel: $catatan (array baris log), $ukuran (byte)
require APP_PATH . '/views/layout/header.php';
?>

<div class="dua-jalur">
  <a href="impor.php" class="jalur">
    <span class="jalur-ikon"><?= ikon('database', 24) ?></span>
    <span class="jalur-teks">
      <strong>Impor data lama (.txt)</strong>
      <small>Pindahkan isi folder data/ ke database</small>
    </span>
    <span class="jalur-angka plus"><?= ikon('arrow', 20) ?></span>
  </a>
</div>

<section class="card">
  <div class="card-kepala">
    <div>
      <h3>Aktivitas terbaru <span class="jumlah"><?= count($catatan) ?></span></h3>
      <p class="muted">Ukuran file log: <?= e(number_format($ukuran / 1024, 1, ',', '.')) ?> KB. Menampilkan 300 baris terakhir.</p>
    </div>
    <div class="aksi">
      <input type="search" class="cari" placeholder="Cari aktivitas..." data-cari="tabelLog">
      <form method="post" class="inline" data-konfirmasi="Kosongkan seluruh log aktivitas?">
        <?= csrfField() ?>
        <input type="hidden" name="aksi" value="kosongkan">
        <button type="submit" class="btn btn-sm btn-danger">Kosongkan</button>
      </form>
    </div>
  </div>

  <div class="tabel-wrap">
    <table id="tabelLog">
      <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aktivitas</th><th>Detail</th></tr></thead>
      <tbody>
        <?php if (count($catatan) === 0): ?>
          <tr data-kosong><td colspan="4" class="kosong">Belum ada aktivitas tercatat.</td></tr>
        <?php endif; ?>
        <?php foreach ($catatan as $c): ?>
          <tr>
            <td class="nowrap"><?= e(tglPendek($c['waktu'], true)) ?></td>
            <td><?= e($c['pelaku']) ?></td>
            <td><strong><?= e($c['aksi']) ?></strong></td>
            <td class="muted"><?= e($c['detail']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require APP_PATH . '/views/layout/footer.php'; ?>
