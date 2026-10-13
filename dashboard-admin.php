<?php
// ============================================================
// dashboard-admin.php
// Admin menangani 2 kasus pesanan:
//   1. Dari aplikasi : pelanggan pesan layanan -> staff / kurir menjemput cucian,
//                      menimbang, lalu mengisi berat di bagian "Perlu dijemput"
//   2. Di kios       : pelanggan datang langsung -> staff mencatatnya
//                      di menu Transaksi (berat langsung diisi)
// Status pesanan bisa diubah lewat dropdown tanpa reload (AJAX).
// ============================================================
require_once 'includes/config.php';
wajibLogin('admin');

// ----- Bagian AJAX: dipanggil oleh JavaScript (fetch), balasannya JSON -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'ubah_status') {
    header('Content-Type: application/json');

    $id = $_POST['id'] ?? '';
    $statusBaru = $_POST['status'] ?? '';

    if (!in_array($statusBaru, $statusProses, true)) {
        echo json_encode(['sukses' => false, 'pesan' => 'Status tidak valid.']);
        exit;
    }

    $orders = loadOrders();
    $ketemu = false;
    $belumDitimbang = false;
    foreach ($orders as $i => $o) {
        if ($o['id'] === $id) {
            if (!sudahDitimbang($o)) {
                $belumDitimbang = true; // berat harus diisi dulu
            } else {
                $orders[$i]['status'] = $statusBaru;
                $ketemu = true;
            }
        }
    }

    if ($belumDitimbang) {
        echo json_encode(['sukses' => false, 'pesan' => 'Isi berat cucian dulu sebelum mengubah status.']);
    } elseif ($ketemu) {
        saveOrders($orders);
        echo json_encode(['sukses' => true, 'pesan' => 'Status diubah menjadi ' . $statusBaru . '.']);
    } else {
        echo json_encode(['sukses' => false, 'pesan' => 'Pesanan tidak ditemukan.']);
    }
    exit;
}

// ----- Isi berat cucian (pesanan dari aplikasi yang sudah dijemput) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'isi_berat') {
    $id = $_POST['id'] ?? '';
    $berat = round((float)str_replace(',', '.', $_POST['berat'] ?? ''), 1); // "2,5" juga boleh

    if ($berat <= 0 || $berat > 500) {
        setFlash('error', 'Berat harus lebih dari 0 dan masuk akal (maksimal 500).');
        redirect('dashboard-admin.php');
    }

    $orders = loadOrders();
    $users = loadUsers();
    $pesan = '';
    foreach ($orders as $i => $o) {
        if ($o['id'] === $id && $o['status'] === 'Menunggu Jemput') {
            $orders[$i]['qty'] = $berat;
            $orders[$i]['status'] = 'Diterima'; // cucian sudah diambil & ditimbang
            $pesan = 'Berat ' . $berat . ' ' . $o['satuan'] . ' tersimpan untuk ' . namaPengguna($o['username'], $users)
                   . '. Total bayar ' . rupiah(totalOrder($orders[$i])) . '.';
        }
    }

    if ($pesan !== '') {
        saveOrders($orders);
        setFlash('sukses', $pesan);
    } else {
        setFlash('error', 'Pesanan tidak ditemukan atau beratnya sudah diisi.');
    }
    redirect('dashboard-admin.php');
}

// ----- Hitung angka ringkasan -----
$orders = loadOrders();
$users = loadUsers();

$hariIni = tglPendek();
$pesananHariIni = 0;
$sedangDiproses = 0;
$pendapatanHariIni = 0;
$perluDijemput = [];

foreach ($orders as $o) {
    // tanggal disimpan "26 Sep 2026 16:43", 11 karakter pertama = tanggalnya saja
    if (substr($o['tanggal'], 0, 11) === $hariIni) {
        $pesananHariIni++;
        $pendapatanHariIni += totalOrder($o); // sudah dipotong diskon, 0 kalau belum ditimbang
    }
    if ($o['status'] === 'Diterima' || $o['status'] === 'Dicuci') {
        $sedangDiproses++;
    }
    if ($o['status'] === 'Menunggu Jemput') {
        $perluDijemput[] = $o;
    }
}

$jumlahPelanggan = 0;
foreach ($users as $u) {
    if ($u['role'] === 'customer') {
        $jumlahPelanggan++;
    }
}

// pesanan yang sudah ditimbang, terbaru di atas
$berjalan = [];
foreach (array_reverse($orders) as $o) {
    if (sudahDitimbang($o)) {
        $berjalan[] = $o;
    }
}
$berjalan = array_slice($berjalan, 0, 8);

$jamSekarang = (int)date('G');
if ($jamSekarang < 11) { $sapa = 'Selamat pagi'; }
elseif ($jamSekarang < 15) { $sapa = 'Selamat siang'; }
elseif ($jamSekarang < 18) { $sapa = 'Selamat sore'; }
else { $sapa = 'Selamat malam'; }

$judulHalaman = 'Dashboard';
$subJudul = 'Ringkasan operasional laundry hari ini.';
$jsTambahan = ['dashboard-admin.js'];
require 'includes/header.php';
?>

<section class="sapaan">
  <div>
    <h2><?= e($sapa) ?>, <?= e($_SESSION['nama']) ?>.</h2>
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
    <div><span class="stat-label">Pesanan hari ini</span><span class="stat-value"><?= $pesananHariIni ?></span></div>
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
        <input type="hidden" name="aksi" value="isi_berat">
        <input type="hidden" name="id" value="<?= e($o['id']) ?>">

        <div class="item-info">
          <div class="item-nama">
            <strong><?= e(namaPengguna($o['username'], $users)) ?></strong>
            <span class="chip"><?= e($o['layanan']) ?></span>
            <?php if ($o['diskon'] > 0): ?><span class="tag-promo">Promo <?= (int)$o['diskon'] ?>%</span><?php endif; ?>
          </div>
          <p class="item-alamat"><?= ikon('pin', 15) ?> <?= e($o['catatan'] !== '' ? $o['catatan'] : 'Alamat tidak dicantumkan') ?></p>
          <small class="muted">Dipesan <?= e($o['tanggal']) ?> &middot; <?= e(rupiah($o['harga'])) ?> / <?= e($o['satuan']) ?></small>
        </div>

        <div class="item-berat">
          <label for="berat-<?= e($o['id']) ?>" class="sr-saja">Berat cucian</label>
          <div class="input-satuan">
            <input type="number" id="berat-<?= e($o['id']) ?>" name="berat" min="0.1" max="500" step="0.1" placeholder="0,0" required>
            <span><?= e($o['satuan']) ?></span>
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
            <td><a href="nota.php?id=<?= e($o['id']) ?>" class="kode"><?= e($o['id']) ?></a></td>
            <td><?= e(namaPengguna($o['username'], $users)) ?></td>
            <td><?= e($o['layanan']) ?></td>
            <td><?= beratLabel($o) ?></td>
            <td class="uang"><?= labelTotal($o) ?></td>
            <td><span class="asal asal-<?= strtolower(sumberOrder($o, $users)) ?>"><?= e(sumberOrder($o, $users)) ?></span></td>
            <td>
              <select class="select-status status-<?= e(statusKelas($o['status'])) ?>" data-id="<?= e($o['id']) ?>">
                <?php foreach ($statusProses as $s): ?>
                  <option value="<?= e($s) ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require 'includes/footer.php'; ?>
