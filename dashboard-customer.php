<?php
// ============================================================
// dashboard-customer.php
// Menampilkan "tiket" pesanan aktif (status, berat, harga, total setelah promo),
// promo yang bisa dipakai, dan 3 transaksi terakhir pelanggan yang sedang login.
// Berat baru muncul setelah staff / kurir menimbang cucian.
// ============================================================
require_once 'includes/config.php';
wajibLogin('customer');

$username = $_SESSION['user'];

// ambil pesanan milik user ini saja, yang terbaru di atas
$pesananSaya = [];
foreach (loadOrders() as $o) {
    if ($o['username'] === $username) {
        $pesananSaya[] = $o;
    }
}
$pesananSaya = array_reverse($pesananSaya);

// cari pesanan yang masih berjalan (belum Selesai / Diambil)
$pesananAktif = null;
foreach ($pesananSaya as $o) {
    if ($o['status'] !== 'Selesai' && $o['status'] !== 'Diambil') {
        $pesananAktif = $o;
        break;
    }
}
$langkahSekarang = $pesananAktif ? array_search($pesananAktif['status'], $daftarStatus) : -1;

// keterangan singkat di bawah tiap langkah
$keterangan = [
    'Menunggu Jemput' => 'Kurir segera datang',
    'Diterima'        => 'Sudah ditimbang',
    'Dicuci'          => 'Sedang dicuci',
    'Selesai'         => 'Siap diambil',
    'Diambil'         => 'Sudah di tanganmu',
];

$terakhir = array_slice($pesananSaya, 0, 3);

$promoAktif = promoAktifList();
$tampilAksi = false;   // kartu promo tanpa tombol edit/hapus
$bisaDipakai = true;   // kartu promo dengan tombol "Pakai promo ini"

$jamSekarang = (int)date('G');
if ($jamSekarang < 11) { $sapa = 'Selamat pagi'; }
elseif ($jamSekarang < 15) { $sapa = 'Selamat siang'; }
elseif ($jamSekarang < 18) { $sapa = 'Selamat sore'; }
else { $sapa = 'Selamat malam'; }

$judulHalaman = 'Dashboard';
$subJudul = 'Pantau cucianmu dari jemput sampai selesai.';
require 'includes/header.php';
?>

<section class="sapaan">
  <div>
    <h2><?= e($sapa) ?>, <?= e($_SESSION['nama']) ?>.</h2>
    <p>
      <?php if (!$pesananAktif): ?>
        Belum ada cucian yang sedang berjalan. Mau dijemput hari ini?
      <?php elseif (!sudahDitimbang($pesananAktif)): ?>
        Pesananmu sudah masuk. Kurir akan menjemput dan menimbang cucianmu.
      <?php else: ?>
        Cucianmu sudah ditimbang dan sedang kami urus.
      <?php endif; ?>
    </p>
  </div>
  <a href="order.php" class="btn btn-primary btn-besar"><?= ikon('plusplain', 18) ?> Buat pesanan</a>
</section>

<?php if ($pesananAktif): ?>
<section class="tiket">
  <div class="tiket-utama">
    <div class="tiket-judul">
      <div>
        <h3><?= e($pesananAktif['layanan']) ?></h3>
        <p class="muted">Dipesan <?= e($pesananAktif['tanggal']) ?></p>
      </div>
      <a href="nota.php?id=<?= e($pesananAktif['id']) ?>" class="kode"><?= e($pesananAktif['id']) ?></a>
    </div>

    <div class="steps">
      <?php foreach ($daftarStatus as $i => $s): ?>
        <?php
          $kelas = '';
          if ($i < $langkahSekarang) { $kelas = 'selesai'; }
          elseif ($i === $langkahSekarang) { $kelas = 'sekarang'; }
        ?>
        <div class="step <?= $kelas ?>">
          <div class="step-lingkaran"><?= $kelas === 'selesai' ? ikon('check', 16) : ($i + 1) ?></div>
          <span class="step-nama"><?= e($s) ?></span>
          <small><?= e($keterangan[$s]) ?></small>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (trim($pesananAktif['catatan']) !== ''): ?>
      <p class="tiket-alamat"><?= ikon('pin', 17) ?> <span><?= e($pesananAktif['catatan']) ?></span></p>
    <?php endif; ?>
  </div>

  <div class="tiket-sobekan">
    <?php if (sudahDitimbang($pesananAktif)): ?>
      <h4>Rincian cucian</h4>
      <dl class="rincian">
        <div><dt>Berat</dt><dd><?= e($pesananAktif['qty']) ?> <?= e($pesananAktif['satuan']) ?></dd></div>
        <div><dt>Harga</dt><dd><?= e(rupiah($pesananAktif['harga'])) ?> / <?= e($pesananAktif['satuan']) ?></dd></div>
        <div><dt>Subtotal</dt><dd><?= e(rupiah(subtotalOrder($pesananAktif))) ?></dd></div>
        <?php if ($pesananAktif['diskon'] > 0): ?>
          <div class="rincian-promo"><dt>Promo <?= e($pesananAktif['promo']) ?> (<?= (int)$pesananAktif['diskon'] ?>%)</dt><dd>-<?= e(rupiah(potonganOrder($pesananAktif))) ?></dd></div>
        <?php endif; ?>
      </dl>
      <div class="rincian-total"><span>Total bayar</span><strong><?= e(rupiah(totalOrder($pesananAktif))) ?></strong></div>
    <?php else: ?>
      <h4>Rincian cucian</h4>
      <div class="belum-timbang">
        <?= ikon('scale', 30) ?>
        <p><strong>Belum ditimbang</strong></p>
        <p class="muted">Berat dan total bayar akan muncul di sini setelah kurir menimbang cucianmu.</p>
        <?php if ($pesananAktif['diskon'] > 0): ?>
          <span class="tag-promo">Promo <?= e($pesananAktif['promo']) ?> (<?= (int)$pesananAktif['diskon'] ?>%) terpasang</span>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php else: ?>
<section class="card kosong-besar">
  <?= ikon('droplet', 40) ?>
  <p>Belum ada pesanan yang sedang berjalan.<br>Pilih layanan, kurir yang urus sisanya.</p>
  <a href="order.php" class="btn btn-primary btn-sm">Buat pesanan</a>
</section>
<?php endif; ?>

<?php if (count($promoAktif) > 0): ?>
  <div class="judul-seksi"><h3>Promo untukmu</h3><p class="muted">Pilih promo, diskonnya otomatis terpasang di form pesanan.</p></div>
  <div class="grid-promo">
    <?php foreach ($promoAktif as $p): include 'includes/kartu_promo.php'; endforeach; ?>
  </div>
<?php endif; ?>

<section class="card">
  <div class="card-kepala">
    <h3>Transaksi terakhir</h3>
    <a href="riwayat.php" class="btn btn-sm btn-ghost">Lihat semua</a>
  </div>
  <div class="tabel-wrap">
    <table>
      <thead><tr><th>Tanggal</th><th>Layanan</th><th>Berat</th><th>Total</th><th>Status</th></tr></thead>
      <tbody>
        <?php if (count($terakhir) === 0): ?>
          <tr data-kosong><td colspan="5" class="kosong">Belum ada transaksi.</td></tr>
        <?php endif; ?>
        <?php foreach ($terakhir as $o): ?>
          <tr>
            <td><?= e($o['tanggal']) ?></td>
            <td><?= e($o['layanan']) ?></td>
            <td><?= beratLabel($o) ?></td>
            <td class="uang"><?= labelTotal($o) ?></td>
            <td><?= badgeStatus($o['status']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require 'includes/footer.php'; ?>
