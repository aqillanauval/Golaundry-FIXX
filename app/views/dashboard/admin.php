<?php
// views/dashboard/admin.php
// Variabel: $sapa, $jumlahHariIni, $pendapatanHariIni, $sedangDiproses, $jumlahPelanggan,
//           $perluDijemput (Pesanan[]), $berjalan (Pesanan[]), $statusProses
require FOLDER_APP . '/views/layout/header.php';
?>

<section class="sapaan">
  <div>
    <h2><?= e($sapa) ?>, <?= e($pengguna->getNama()) ?>.</h2>
    <p>
      <?php if (count($perluDijemput) > 0): ?>
        Ada <strong><?= count($perluDijemput) ?> cucian</strong> yang menunggu dijemput dan ditimbang.
      <?php else: ?>
        Tidak ada jemputan yang tertunda. Kerja bagus!
      <?php endif; ?>
    </p>
  </div>
</section>

<div class="dua-jalur">
  <a href="#jemputan" class="jalur">
    <span class="jalur-ikon"><?= ikon('truck', 24) ?></span>
    <span class="jalur-teks">
      <strong>Pesanan dari aplikasi</strong>
      <small>Jemput cucian, timbang, lalu isi beratnya</small>
    </span>
    <span class="jalur-angka"><?= count($perluDijemput) ?></span>
  </a>
  <a href="transaksi.php" class="jalur">
    <span class="jalur-ikon kios"><?= ikon('store', 24) ?></span>
    <span class="jalur-teks">
      <strong>Pelanggan datang ke kios</strong>
      <small>Catat langsung di menu Transaksi</small>
    </span>
    <span class="jalur-angka plus"><?= ikon('plusplain', 20) ?></span>
  </a>
</div>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-ikon biru"><?= ikon('cart', 22) ?></div>
    <div><span class="stat-label">Pesanan hari ini</span><span class="stat-value"><?= $jumlahHariIni ?></span></div>
  </div>
  <div class="stat-card">
    <div class="stat-ikon kuning"><?= ikon('droplet', 22) ?></div>
    <div><span class="stat-label">Sedang diproses</span><span class="stat-value"><?= $sedangDiproses ?></span></div>
  </div>
  <div class="stat-card">
    <div class="stat-ikon hijau"><?= ikon('wallet', 22) ?></div>
    <div><span class="stat-label">Pendapatan hari ini</span><span class="stat-value"><?= e(rupiah($pendapatanHariIni)) ?></span></div>
  </div>
  <div class="stat-card">
    <div class="stat-ikon ungu"><?= ikon('users', 22) ?></div>
    <div><span class="stat-label">Jumlah pelanggan</span><span class="stat-value"><?= $jumlahPelanggan ?></span></div>
  </div>
</div>

<section class="card" id="jemputan">
  <div class="card-kepala">
    <div>
      <h3>Perlu dijemput <span class="jumlah"><?= count($perluDijemput) ?></span></h3>
      <p class="muted">Setelah cucian diambil dan ditimbang, isi beratnya. Total bayar langsung muncul di dashboard pelanggan.</p>
    </div>
  </div>

  <?php if (count($perluDijemput) === 0): ?>
    <div class="kosong-kecil"><?= ikon('check', 22) ?> Semua pesanan dari aplikasi sudah dijemput.</div>
  <?php endif; ?>

  <div class="daftar-jemput">
    <?php foreach ($perluDijemput as $o): ?>
      <form method="post" action="dashboard-admin.php" class="item-jemput">
        <?= inputToken() ?>
        <input type="hidden" name="aksi" value="isi_berat">
        <input type="hidden" name="id" value="<?= e($o->kode) ?>">

        <div class="item-info">
          <div class="item-nama">
            <strong><?= e($o->pelanggan) ?></strong>
            <span class="chip"><?= e($o->nama_layanan) ?></span>
            <?php if ($o->punyaDiskon()): ?><span class="tag-promo">Promo <?= (int)$o->diskon ?>%</span><?php endif; ?>
          </div>
          <p class="item-alamat"><?= ikon('pin', 15) ?> <?= e($o->catatan !== '' ? $o->catatan : 'Alamat tidak dicantumkan') ?></p>
          <small class="muted">Dipesan <?= e($o->tanggalTampil()) ?> &middot; <?= e(rupiah($o->harga)) ?> / <?= e($o->satuan) ?></small>
        </div>

        <div class="item-berat">
          <label for="berat-<?= e($o->kode) ?>" class="sr-saja">Berat cucian</label>
          <div class="input-satuan">
            <input type="number" id="berat-<?= e($o->kode) ?>" name="berat" min="0.1" max="500" step="0.1" placeholder="0,0" required>
            <span><?= e($o->satuan) ?></span>
          </div>
          <button type="submit" class="btn btn-primary btn-sm">Simpan berat</button>
        </div>
      </form>
    <?php endforeach; ?>
  </div>
</section>

<section class="card">
  <div class="card-kepala">
    <div>
      <h3>Pesanan berjalan</h3>
      <p class="muted">Ubah status lewat dropdown, langsung tersimpan tanpa reload.</p>
    </div>
    <a href="transaksi.php" class="btn btn-sm btn-ghost">Lihat semua</a>
  </div>

  <div class="tabel-wrap">
    <table>
      <thead>
        <tr>
          <th>No. Nota</th><th>Pelanggan</th><th>Layanan</th><th>Berat</th>
          <th>Total</th><th>Asal</th><th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($berjalan) === 0): ?>
          <tr data-kosong><td colspan="7" class="kosong">Belum ada pesanan yang ditimbang.</td></tr>
        <?php endif; ?>
        <?php foreach ($berjalan as $o): ?>
          <tr>
            <td><a href="nota.php?id=<?= e($o->kode) ?>" class="kode"><?= e($o->kode) ?></a></td>
            <td><?= e($o->pelanggan) ?></td>
            <td><?= e($o->nama_layanan) ?></td>
            <td><?= $o->labelBerat() ?></td>
            <td class="uang"><?= $o->labelTotal() ?></td>
            <td><span class="asal asal-<?= e($o->sumber) ?>"><?= e($o->asal()) ?></span></td>
            <td>
              <select class="select-status status-<?= e(statusKelas($o->status)) ?>" data-id="<?= e($o->kode) ?>">
                <?php foreach ($statusProses as $s): ?>
                  <option value="<?= e($s) ?>" <?= $o->status === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require FOLDER_APP . '/views/layout/footer.php'; ?>
