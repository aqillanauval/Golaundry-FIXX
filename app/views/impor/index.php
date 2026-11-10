<?php
// views/impor/index.php  -  Variabel: $ringkasan, $hasil (null / jumlah per tabel), $error
require FOLDER_APP . '/views/layout/header.php';
?>

<?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<?php if ($hasil !== null): ?>
  <div class="alert alert-sukses">
    Impor selesai. Data baru:
    <?php foreach ($hasil as $tabel => $n): ?>
      <strong><?= e($tabel) ?></strong> <?= (int)$n ?> &nbsp;
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<section class="card">
  <h3>Isi folder data/</h3>
  <p class="muted">Data yang sudah ada di database (username, nama layanan, judul promo, atau No. Nota yang sama) otomatis dilewati, jadi impor aman diulang. Seluruh proses berjalan dalam satu transaksi: kalau satu baris gagal, semuanya dibatalkan.</p>

  <div class="tabel-wrap">
    <table>
      <thead><tr><th>File</th><th>Ada?</th><th>Ukuran</th><th>Jumlah baris</th></tr></thead>
      <tbody>
        <?php foreach ($ringkasan as $r): ?>
          <tr>
            <td><strong><?= e($r['file']) ?></strong></td>
            <td><?= $r['ada'] ? 'Ya' : '<span class="muted">Tidak</span>' ?></td>
            <td><?= e(number_format($r['ukuran'], 0, ',', '.')) ?> byte</td>
            <td><?= (int)$r['baris'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <form method="post" action="impor.php" class="form-aksi kecil-atas" data-konfirmasi="Mulai impor data dari file .txt ke database?">
    <?= inputToken() ?>
    <input type="hidden" name="aksi" value="impor">
    <button type="submit" class="btn btn-primary"><?= ikon('database', 18) ?> Impor sekarang</button>
    <a href="log.php" class="btn btn-ghost">Kembali</a>
  </form>
</section>

<?php require FOLDER_APP . '/views/layout/footer.php'; ?>
