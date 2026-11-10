<?php
// views/transaksi/index.php
// Variabel: $error, $form, $modeEdit, $editMenunggu, $diskonTetap, $namaPromoTetap,
//           $layananList, $promoAktif, $statusProses, $semua (Pesanan[]), $cari,
//           $totalPendapatan, $jumlahMenunggu
require APP_PATH . '/views/layout/header.php';
?>

<div class="stat-grid dua">
  <div class="stat-card">
    <div class="stat-ikon biru"><?= ikon('file', 22) ?></div>
    <div><span class="stat-label"><?= $cari !== '' ? 'Hasil pencarian' : 'Total transaksi' ?></span><span class="stat-value"><?= count($semua) ?></span></div>
  </div>
  <div class="stat-card">
    <div class="stat-ikon hijau"><?= ikon('wallet', 22) ?></div>
    <div><span class="stat-label">Total pendapatan</span><span class="stat-value"><?= e(rupiah($totalPendapatan)) ?></span></div>
  </div>
</div>

<section class="card sorot" id="formTransaksi">
  <h3>
    <?php if (!$modeEdit): ?>Pelanggan datang ke kios
    <?php elseif ($editMenunggu): ?>Isi berat cucian
    <?php else: ?>Edit transaksi<?php endif; ?>
  </h3>
  <p class="muted">
    <?php if ($editMenunggu): ?>
      Pesanan dari aplikasi ini sudah dijemput. Masukkan hasil timbangan, lalu statusnya jadi Diterima.
    <?php else: ?>
      Catat cucian pelanggan yang datang langsung. Berat cucian langsung ditimbang dan diisi di sini.
    <?php endif; ?>
  </p>

  <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="transaksi.php" class="form">
    <?= csrfField() ?>
    <input type="hidden" name="aksi" value="simpan">
    <input type="hidden" name="id" value="<?= e($form['id']) ?>">

    <div class="form-grid">
      <div class="field">
        <label for="nama_pelanggan">Nama pelanggan</label>
        <input type="text" id="nama_pelanggan" name="nama_pelanggan" maxlength="100" value="<?= e($form['nama_pelanggan']) ?>"
               placeholder="Nama pelanggan" <?= $modeEdit ? 'readonly' : 'required' ?>>
      </div>
      <div class="field">
        <label for="layanan">Layanan</label>
        <select id="layanan" name="layanan" required>
          <option value="" data-harga="0">-- Pilih layanan --</option>
          <?php foreach ($layananList as $l): ?>
            <option value="<?= (int)$l['id'] ?>" data-harga="<?= (int)$l['harga'] ?>" <?= (string)$form['layanan'] === (string)$l['id'] ? 'selected' : '' ?>>
              <?= e($l['nama']) ?> - <?= e(rupiah($l['harga'])) ?> / <?= e($l['satuan']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="qty">Berat / jumlah (kg atau item)</label>
        <input type="number" id="qty" name="qty" min="0.1" max="500" step="0.1" placeholder="Hasil timbangan"
               value="<?= $form['qty'] !== '' ? e(str_replace(',', '.', (string)$form['qty'])) : '' ?>" required>
      </div>

      <?php if (!$editMenunggu): ?>
      <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
          <?php foreach ($statusProses as $s): ?>
            <option value="<?= e($s) ?>" <?= $form['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>

      <?php if (!$modeEdit): ?>
      <div class="field">
        <label for="promo">Promo (opsional)</label>
        <select id="promo" name="promo">
          <option value="" data-diskon="0">Tanpa promo</option>
          <?php foreach ($promoAktif as $p): ?>
            <option value="<?= (int)$p['id'] ?>" data-diskon="<?= (int)$p['diskon'] ?>" <?= $form['promo'] === (string)$p['id'] ? 'selected' : '' ?>>
              <?= e($p['judul']) ?> (<?= (int)$p['diskon'] ?>%)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>

      <div class="field penuh">
        <label for="catatan"><?= $editMenunggu ? 'Alamat jemput / catatan' : 'Catatan (boleh dikosongkan)' ?></label>
        <input type="text" id="catatan" name="catatan" maxlength="500" value="<?= e($form['catatan']) ?>">
      </div>
    </div>

    <?php if ($modeEdit && $diskonTetap > 0): ?>
      <p class="muted">Transaksi ini memakai promo <strong><?= e($namaPromoTetap) ?></strong> (<?= (int)$diskonTetap ?>%). Diskonnya tetap.</p>
    <?php endif; ?>

    <div class="estimasi">Total bayar <strong id="estimasi" data-diskon-tetap="<?= (int)$diskonTetap ?>">Rp 0</strong></div>

    <div class="form-aksi">
      <button type="submit" class="btn btn-primary">
        <?= $editMenunggu ? 'Simpan berat' : ($modeEdit ? 'Simpan perubahan' : 'Catat transaksi') ?>
      </button>
      <?php if ($modeEdit): ?><a href="transaksi.php" class="btn btn-ghost">Batal</a><?php endif; ?>
    </div>
  </form>
</section>

<section class="card">
  <div class="card-kepala">
    <h3>Riwayat transaksi</h3>
    <!-- Pencarian ke server (GET ?cari=) + saring langsung di tabel (data-cari) -->
    <form method="get" action="transaksi.php" class="inline">
      <input type="search" name="cari" class="cari" value="<?= e($cari) ?>" placeholder="Cari nota, nama, layanan..." data-cari="tabelTransaksi">
    </form>
  </div>

  <?php if ($cari !== ''): ?>
    <p class="muted">Hasil pencarian untuk <strong>"<?= e($cari) ?>"</strong>: <?= count($semua) ?> transaksi. <a href="transaksi.php">Tampilkan semua</a></p>
  <?php endif; ?>

  <div class="filter-chip" id="filterTransaksi">
    <button type="button" class="aktif" data-filter="semua">Semua</button>
    <button type="button" data-filter="aplikasi">Dari aplikasi</button>
    <button type="button" data-filter="kios">Di kios</button>
    <button type="button" data-filter="menunggu">Perlu dijemput <?php if ($jumlahMenunggu > 0): ?><span class="titik-angka"><?= $jumlahMenunggu ?></span><?php endif; ?></button>
  </div>

  <div class="tabel-wrap">
    <table id="tabelTransaksi">
      <thead>
        <tr><th>No. Nota</th><th>Pelanggan</th><th>Layanan</th><th>Berat</th><th>Total</th><th>Asal</th><th>Tanggal</th><th>Status</th><th class="kanan">Aksi</th></tr>
      </thead>
      <tbody>
        <?php if (count($semua) === 0): ?>
          <tr data-kosong><td colspan="9" class="kosong"><?= $cari !== '' ? 'Tidak ada transaksi yang cocok.' : 'Belum ada transaksi.' ?></td></tr>
        <?php endif; ?>
        <?php foreach ($semua as $o): ?>
          <tr data-asal="<?= e($o->sumber) ?>" data-status="<?= $o->menungguJemput() ? 'menunggu' : 'proses' ?>">
            <td><a href="nota.php?id=<?= e($o->kode) ?>" class="kode"><?= e($o->kode) ?></a></td>
            <td><?= e($o->pelanggan) ?></td>
            <td><?= e($o->nama_layanan) ?></td>
            <td><?= $o->labelBerat() ?></td>
            <td class="uang"><?= $o->labelTotal() ?></td>
            <td><span class="asal asal-<?= e($o->sumber) ?>"><?= e($o->asal()) ?></span></td>
            <td><?= e($o->tanggalTampil(false)) ?></td>
            <td><?= badgeStatus($o->status) ?></td>
            <td class="aksi">
              <a href="transaksi.php?edit=<?= e($o->kode) ?>#formTransaksi" class="btn btn-sm <?= $o->menungguJemput() ? 'btn-primary' : 'btn-ghost' ?>">
                <?= $o->menungguJemput() ? 'Isi berat' : 'Edit' ?>
              </a>
              <?= formHapus($o->kode, 'Yakin hapus transaksi ini?') ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require APP_PATH . '/views/layout/footer.php'; ?>
