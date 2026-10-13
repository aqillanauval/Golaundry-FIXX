<?php
// ============================================================
// nota.php  -  Nota pembayaran, bisa dicetak / disimpan jadi PDF
// Dibuka lewat link: nota.php?id=ORDxxxx   (data dibaca dari $_GET)
// Pelanggan hanya boleh membuka nota miliknya sendiri, admin boleh semua.
// ============================================================
require_once 'includes/config.php';
wajibLogin();

$id = $_GET['id'] ?? '';
$order = null;
foreach (loadOrders() as $o) {
    if ($o['id'] === $id) {
        $order = $o;
    }
}

// pelanggan tidak boleh melihat nota orang lain
if ($order && $_SESSION['role'] === 'customer' && $order['username'] !== $_SESSION['user']) {
    $order = null;
}

$linkKembali = ($_SESSION['role'] === 'admin') ? 'transaksi.php' : 'riwayat.php';
$users = loadUsers();
?>
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
      <?php if ($order && sudahDitimbang($order)): ?>
        <button type="button" class="btn btn-primary" data-cetak><?= ikon('printer', 18) ?> Cetak / simpan PDF</button>
      <?php endif; ?>
    </div>

    <?php if (!$order): ?>
      <div class="card kosong-besar"><p>Nota tidak ditemukan.</p></div>
    <?php elseif (!sudahDitimbang($order)): ?>
      <div class="card kosong-besar">
        <?= ikon('scale', 40) ?>
        <p><strong>Nota belum tersedia.</strong><br>Cucian belum ditimbang. Nota muncul setelah staff mengisi beratnya.</p>
      </div>
    <?php else: ?>
      <div class="nota-box">
        <div class="nota-kepala">
          <?= logoSvg(46) ?>
          <div>
            <h2>GOLaundry</h2>
            <p>Nota Pembayaran Laundry</p>
          </div>
        </div>

        <div class="nota-info">
          <div>
            <p><span>No. Nota</span><strong><?= e($order['id']) ?></strong></p>
            <p><span>Tanggal</span><strong><?= e($order['tanggal']) ?></strong></p>
          </div>
          <div>
            <p><span>Pelanggan</span><strong><?= e(namaPengguna($order['username'], $users)) ?></strong></p>
            <p><span>Status</span><?= badgeStatus($order['status']) ?></p>
          </div>
        </div>

        <table>
          <thead><tr><th>Layanan</th><th>Jumlah</th><th>Harga satuan</th><th class="kanan">Subtotal</th></tr></thead>
          <tbody>
            <tr>
              <td><?= e($order['layanan']) ?></td>
              <td><?= e($order['qty']) ?> <?= e($order['satuan']) ?></td>
              <td><?= e(rupiah($order['harga'])) ?></td>
              <td class="kanan"><?= e(rupiah(subtotalOrder($order))) ?></td>
            </tr>
          </tbody>
        </table>

        <?php if ($order['catatan'] !== ''): ?>
          <p class="nota-catatan"><strong>Alamat / catatan:</strong> <?= e($order['catatan']) ?></p>
        <?php endif; ?>

        <?php if ($order['diskon'] > 0): ?>
          <div class="nota-baris">
            <span>Subtotal</span>
            <span><?= e(rupiah(subtotalOrder($order))) ?></span>
          </div>
          <div class="nota-baris nota-diskon">
            <span>Promo <?= e($order['promo']) ?> (<?= (int)$order['diskon'] ?>%)</span>
            <span>- <?= e(rupiah(potonganOrder($order))) ?></span>
          </div>
        <?php endif; ?>

        <div class="nota-total">
          <span>Total bayar</span>
          <strong><?= e(rupiah(totalOrder($order))) ?></strong>
        </div>

        <p class="nota-footer">Terima kasih sudah memakai GOLaundry!</p>
      </div>
    <?php endif; ?>

  </div>
  <script src="assets/js/app.js"></script>
</body>
</html>
