<?php // views/nota/index.php  -  Variabel: $pesanan (Pesanan|null), $linkKembali ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nota - GOLaundry</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="icon" type="image/png" href="assets/img/logo.png">
  <script src="https://kit.fontawesome.com/b7c9988b7a.js" crossorigin="anonymous"></script>
</head>
<body class="halaman-nota">
  <div class="nota-wrapper">

    <div class="nota-aksi tidak-dicetak">
      <a href="<?= e($linkKembali) ?>" class="btn btn-ghost">&larr; Kembali</a>
      <?php if ($pesanan && $pesanan->sudahDitimbang()): ?>
        <button type="button" class="btn btn-primary" data-cetak><?= ikon('printer', 18) ?> Cetak / simpan PDF</button>
      <?php endif; ?>
    </div>

    <?php if (!$pesanan): ?>
      <div class="card kosong-besar"><p>Nota tidak ditemukan.</p></div>
    <?php elseif (!$pesanan->sudahDitimbang()): ?>
      <div class="card kosong-besar">
        <?= ikon('scale', 40) ?>
        <p><strong>Nota belum tersedia.</strong><br>Cucian belum ditimbang. Nota muncul setelah staff mengisi beratnya.</p>
      </div>
    <?php else: ?>
      <div class="nota-box">
        <div class="nota-kepala">
          <?= logoImg(46) ?>
          <div>
            <h2>GOLaundry</h2>
            <p>Nota Pembayaran Laundry</p>
          </div>
        </div>

        <div class="nota-info">
          <div>
            <p><span>No. Nota</span><strong><?= e($pesanan->kode) ?></strong></p>
            <p><span>Tanggal</span><strong><?= e($pesanan->tanggalTampil()) ?></strong></p>
          </div>
          <div>
            <p><span>Pelanggan</span><strong><?= e($pesanan->pelanggan) ?></strong></p>
            <p><span>Status</span><?= badgeStatus($pesanan->status) ?></p>
          </div>
        </div>

        <table>
          <thead><tr><th>Layanan</th><th>Jumlah</th><th>Harga satuan</th><th class="kanan">Subtotal</th></tr></thead>
          <tbody>
            <tr>
              <td><?= e($pesanan->nama_layanan) ?></td>
              <td><?= e(formatBerat($pesanan->berat)) ?> <?= e($pesanan->satuan) ?></td>
              <td><?= e(rupiah($pesanan->harga)) ?></td>
              <td class="kanan"><?= e(rupiah($pesanan->subtotal())) ?></td>
            </tr>
          </tbody>
        </table>

        <?php if ((string)$pesanan->catatan !== ''): ?>
          <p class="nota-catatan"><strong>Alamat / catatan:</strong> <?= e($pesanan->catatan) ?></p>
        <?php endif; ?>

        <?php if ($pesanan->punyaDiskon()): ?>
          <div class="nota-baris">
            <span>Subtotal</span>
            <span><?= e(rupiah($pesanan->subtotal())) ?></span>
          </div>
          <div class="nota-baris nota-diskon">
            <span>Promo <?= e($pesanan->nama_promo) ?> (<?= (int)$pesanan->diskon ?>%)</span>
            <span>- <?= e(rupiah($pesanan->potongan())) ?></span>
          </div>
        <?php endif; ?>

        <div class="nota-total">
          <span>Total bayar</span>
          <strong><?= e(rupiah($pesanan->total())) ?></strong>
        </div>

        <p class="nota-footer">Terima kasih sudah memakai GOLaundry!</p>
      </div>
    <?php endif; ?>

  </div>
  <script src="assets/js/app.js"></script>
</body>
</html>
