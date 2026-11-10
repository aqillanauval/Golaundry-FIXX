<?php
// views/order/index.php
// Variabel: $error, $form, $modeEdit, $layananList, $promoAktif, $promoLama, $pesananSaya (Pesanan[])
require FOLDER_APP . '/views/layout/header.php';
?>

<?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<form method="post" action="order.php" class="order-grid" id="formPesan">
  <?= inputToken() ?>
  <input type="hidden" name="aksi" value="simpan">
  <input type="hidden" name="id" value="<?= e($form['id']) ?>">

  <div class="kolom-form">

    <!-- Langkah 1 -->
    <section class="card langkah">
      <h3><span class="nomor">1</span> Pilih layanan</h3>
      <?php if (count($layananList) === 0): ?>
        <p class="muted">Belum ada layanan yang tersedia. Silakan coba lagi nanti.</p>
      <?php endif; ?>
      <div class="pilihan-grid">
        <?php foreach ($layananList as $l): ?>
          <label class="opsi">
            <input type="radio" name="layanan" value="<?= (int)$l['id'] ?>"
                   data-nama="<?= e($l['nama']) ?>" data-harga="<?= (int)$l['harga'] ?>" data-satuan="<?= e($l['satuan']) ?>"
                   <?= (string)$form['layanan'] === (string)$l['id'] ? 'checked' : '' ?>>
            <span class="isi isi-layanan">
              <span class="opsi-ikon"><?= ikon('shirt', 22) ?></span>
              <strong><?= e($l['nama']) ?></strong>
              <small><?= e(rupiah($l['harga'])) ?> / <?= e($l['satuan']) ?></small>
            </span>
          </label>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Langkah 2 -->
    <section class="card langkah">
      <h3><span class="nomor">2</span> Alamat jemput</h3>
      <div class="field">
        <label for="catatan">Alamat lengkap dan catatan untuk kurir</label>
        <textarea id="catatan" name="catatan" rows="3" maxlength="500" required placeholder="Contoh: Kos Melati no. 5, dekat gerbang kampus. Pisahkan baju putih."><?= e($form['catatan']) ?></textarea>
      </div>
      <p class="info-berat"><?= ikon('scale', 18) ?> <span>Tidak perlu menimbang sendiri. Kurir akan menimbang cucianmu saat menjemput, lalu berat dan total bayar muncul di dashboard.</span></p>
    </section>

    <!-- Langkah 3 -->
    <section class="card langkah">
      <h3><span class="nomor">3</span> Pakai promo <span class="opsional">opsional</span></h3>
      <div class="pilihan-promo">
        <label class="opsi">
          <input type="radio" name="promo" value="" data-diskon="0" data-nama="" <?= $form['promo'] === '' ? 'checked' : '' ?>>
          <span class="isi isi-promo isi-tanpa"><strong>Tanpa promo</strong></span>
        </label>

        <?php if ($promoLama): ?>
          <label class="opsi">
            <input type="radio" name="promo" value="lama" data-diskon="<?= (int)$promoLama['diskon'] ?>" data-nama="<?= e($promoLama['judul']) ?>"
                   <?= $form['promo'] === 'lama' ? 'checked' : '' ?>>
            <span class="isi isi-promo">
              <span class="lencana"><?= (int)$promoLama['diskon'] ?>%</span>
              <span class="teks"><strong><?= e($promoLama['judul']) ?></strong><small>Promo dari pesanan ini</small></span>
            </span>
          </label>
        <?php endif; ?>

        <?php foreach ($promoAktif as $p): ?>
          <label class="opsi">
            <input type="radio" name="promo" value="<?= (int)$p['id'] ?>" data-diskon="<?= (int)$p['diskon'] ?>" data-nama="<?= e($p['judul']) ?>"
                   <?= $form['promo'] === (string)$p['id'] ? 'checked' : '' ?>>
            <span class="isi isi-promo">
              <span class="lencana"><?= (int)$p['diskon'] ?>%</span>
              <span class="teks"><strong><?= e($p['judul']) ?></strong><small>Berlaku sampai <?= e(tglPendek($p['berlaku_sampai'])) ?></small></span>
            </span>
          </label>
        <?php endforeach; ?>
      </div>
      <?php if (count($promoAktif) === 0 && !$promoLama): ?>
        <p class="muted kecil-atas">Belum ada promo yang berlaku saat ini.</p>
      <?php endif; ?>
    </section>
  </div>

  <!-- Ringkasan (seperti tiket kecil) -->
  <aside class="ringkasan">
    <h3>Ringkasan pesanan</h3>
    <dl>
      <div><dt>Layanan</dt><dd id="rLayanan">Belum dipilih</dd></div>
      <div><dt>Harga normal</dt><dd id="rHarga">-</dd></div>
      <div class="baris-diskon" id="rBarisDiskon" hidden><dt id="rNamaPromo">Promo</dt><dd id="rDiskon">-</dd></div>
      <div><dt>Berat cucian</dt><dd class="redup">Ditimbang kurir</dd></div>
    </dl>
    <div class="ringkasan-total">
      <span>Harga per <span id="rSatuan">kg</span></span>
      <strong id="rTotal">-</strong>
    </div>
    <p class="catatan-kecil">Total bayar dihitung setelah cucianmu ditimbang: berat x harga di atas.</p>
    <button type="submit" class="btn btn-primary btn-block" id="tombolPesan"><?= $modeEdit ? 'Simpan perubahan' : 'Buat pesanan' ?></button>
    <?php if ($modeEdit): ?><a href="order.php" class="btn btn-ghost btn-block">Batal edit</a><?php endif; ?>
  </aside>
</form>

<section class="card">
  <div class="card-kepala">
    <h3>Pesanan saya <span class="jumlah"><?= count($pesananSaya) ?></span></h3>
    <input type="search" class="cari" placeholder="Cari pesanan..." data-cari="tabelPesanan">
  </div>
  <div class="tabel-wrap">
    <table id="tabelPesanan">
      <thead><tr><th>Tanggal</th><th>Layanan</th><th>Berat</th><th>Total</th><th>Status</th><th class="kanan">Aksi</th></tr></thead>
      <tbody>
        <?php if (count($pesananSaya) === 0): ?>
          <tr data-kosong><td colspan="6" class="kosong">Belum ada pesanan. Pilih layanan di atas untuk mulai.</td></tr>
        <?php endif; ?>
        <?php foreach ($pesananSaya as $o): ?>
          <tr>
            <td><?= e($o->tanggalTampil()) ?></td>
            <td><?= e($o->nama_layanan) ?></td>
            <td><?= $o->labelBerat() ?></td>
            <td class="uang"><?= $o->labelTotal() ?></td>
            <td><?= badgeStatus($o->status) ?></td>
            <td class="aksi">
              <?php if ($o->menungguJemput()): ?>
                <a href="order.php?edit=<?= e($o->kode) ?>" class="btn btn-sm btn-ghost">Edit</a>
                <?= formHapus($o->kode, 'Yakin batalin pesanan ini?', 'Batalkan') ?>
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

<?php require FOLDER_APP . '/views/layout/footer.php'; ?>
