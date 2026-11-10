<?php
// views/dashboard/customer.php
// Variabel: $sapa, $aktif (Pesanan|null), $langkahSekarang, $daftarStatus, $keterangan,
//           $terakhir (Pesanan[]), $promoAktif (array)
require FOLDER_APP . '/views/layout/header.php';
$bisaDipakai = true; // kartu promo dengan tombol "Pakai promo ini"
?>

<section class="sapaan">
  <div>
    <h2><?= e($sapa) ?>, <?= e($pengguna->getNama()) ?>.</h2>
    <p>
      <?php if (!$aktif): ?>
        Belum ada cucian yang sedang berjalan. Mau dijemput hari ini?
      <?php elseif (!$aktif->sudahDitimbang()): ?>
        Pesananmu sudah masuk. Kurir akan menjemput dan menimbang cucianmu.
      <?php else: ?>
        Cucianmu sudah ditimbang dan sedang kami urus.
      <?php endif; ?>
    </p>
  </div>
  <a href="order.php" class="btn btn-primary btn-besar"><?= ikon('plusplain', 18) ?> Buat pesanan</a>
</section>

<?php if ($aktif): ?>
<section class="tiket">
  <div class="tiket-utama">
    <div class="tiket-judul">
      <div>
        <h3><?= e($aktif->nama_layanan) ?></h3>
        <p class="muted">Dipesan <?= e($aktif->tanggalTampil()) ?></p>
      </div>
      <a href="nota.php?id=<?= e($aktif->kode) ?>" class="kode"><?= e($aktif->kode) ?></a>
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

    <?php if (trim((string)$aktif->catatan) !== ''): ?>
      <p class="tiket-alamat"><?= ikon('pin', 17) ?> <span><?= e($aktif->catatan) ?></span></p>
    <?php endif; ?>
  </div>

  <div class="tiket-sobekan">
    <h4>Rincian cucian</h4>
    <?php if ($aktif->sudahDitimbang()): ?>
      <dl class="rincian">
        <div><dt>Berat</dt><dd><?= e(formatBerat($aktif->berat)) ?> <?= e($aktif->satuan) ?></dd></div>
        <div><dt>Harga</dt><dd><?= e(rupiah($aktif->harga)) ?> / <?= e($aktif->satuan) ?></dd></div>
        <div><dt>Subtotal</dt><dd><?= e(rupiah($aktif->subtotal())) ?></dd></div>
        <?php if ($aktif->punyaDiskon()): ?>
          <div class="rincian-promo"><dt>Promo <?= e($aktif->nama_promo) ?> (<?= (int)$aktif->diskon ?>%)</dt><dd>-<?= e(rupiah($aktif->potongan())) ?></dd></div>
        <?php endif; ?>
      </dl>
      <div class="rincian-total"><span>Total bayar</span><strong><?= e(rupiah($aktif->total())) ?></strong></div>
    <?php else: ?>
      <div class="belum-timbang">
        <?= ikon('scale', 30) ?>
        <p><strong>Belum ditimbang</strong></p>
        <p class="muted">Berat dan total bayar akan muncul di sini setelah kurir menimbang cucianmu.</p>
        <?php if ($aktif->punyaDiskon()): ?>
          <span class="tag-promo">Promo <?= e($aktif->nama_promo) ?> (<?= (int)$aktif->diskon ?>%) terpasang</span>
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
    <?php foreach ($promoAktif as $p): include FOLDER_APP . '/views/partials/kartu_promo.php'; endforeach; ?>
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
            <td><?= e($o->tanggalTampil()) ?></td>
            <td><?= e($o->nama_layanan) ?></td>
            <td><?= $o->labelBerat() ?></td>
            <td class="uang"><?= $o->labelTotal() ?></td>
            <td><?= badgeStatus($o->status) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require FOLDER_APP . '/views/layout/footer.php'; ?>
