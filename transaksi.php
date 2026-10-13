<?php
// ============================================================
// transaksi.php  -  CRUD Transaksi (khusus admin)
// Dipakai untuk 2 hal:
//   1. Mencatat pelanggan yang datang langsung ke kios
//      (berat langsung diisi, status mulai dari "Diterima")
//   2. Melihat, mengedit, dan menghapus semua transaksi, termasuk
//      pesanan dari aplikasi. Pesanan aplikasi yang belum ditimbang
//      bisa diisi beratnya lewat tombol "Isi berat".
// Admin juga bisa memilih promo yang masih berlaku untuk transaksi baru.
// ============================================================
require_once 'includes/config.php';
wajibLogin('admin');

$error = '';
$form = ['id' => '', 'nama_pelanggan' => '', 'layanan' => '', 'qty' => '', 'status' => 'Diterima', 'catatan' => '', 'promo' => ''];
$diskonTetap = 0; // diskon dari transaksi yang sedang diedit (tidak diubah saat edit)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $orders = loadOrders();

    // ----- CREATE & UPDATE -----
    if ($aksi === 'simpan') {
        $form['id'] = $_POST['id'] ?? '';
        $form['nama_pelanggan'] = bersihkan($_POST['nama_pelanggan'] ?? '');
        $form['layanan'] = $_POST['layanan'] ?? '';
        $form['qty'] = floatval(str_replace(',', '.', $_POST['qty'] ?? '0'));
        $form['status'] = $_POST['status'] ?? 'Diterima';
        $form['catatan'] = bersihkan($_POST['catatan'] ?? '');
        $form['promo'] = $_POST['promo'] ?? '';

        $layananDipilih = cariLayanan($form['layanan']);
        $sedangEdit = ($form['id'] !== '');

        // transaksi yang diedit: cari data aslinya
        $orderLama = null;
        if ($sedangEdit) {
            foreach ($orders as $o) {
                if ($o['id'] === $form['id']) {
                    $orderLama = $o;
                }
            }
        }
        $masihMenunggu = ($orderLama !== null && $orderLama['status'] === 'Menunggu Jemput');

        // pesanan yang masih Menunggu Jemput: begitu berat diisi, statusnya otomatis jadi Diterima
        if ($masihMenunggu) {
            $form['status'] = 'Diterima';
        }

        // promo hanya dipilih saat membuat transaksi baru. Saat edit, promo lama dibiarkan.
        $diskon = 0;
        $namaPromo = '';
        $promoError = '';
        if (!$sedangEdit && $form['promo'] !== '') {
            $p = cariPromo($form['promo']);
            if (!$p || !promoMasihBerlaku($p)) {
                $promoError = 'Promo tidak valid atau sudah berakhir.';
            } else {
                $diskon = $p['diskon'];
                $namaPromo = $p['judul'];
            }
        }

        if ($sedangEdit && $orderLama === null) {
            $error = 'Transaksi tidak ditemukan.';
        } elseif (!$sedangEdit && $form['nama_pelanggan'] === '') {
            $error = 'Nama pelanggan wajib diisi.';
        } elseif (!$layananDipilih) {
            $error = 'Silakan pilih layanan yang valid.';
        } elseif ($form['qty'] <= 0 || $form['qty'] > 500) {
            $error = 'Berat harus lebih dari 0 dan masuk akal (maksimal 500).';
        } elseif (!in_array($form['status'], $statusProses, true)) {
            $error = 'Status tidak valid.';
        } elseif ($promoError !== '') {
            $error = $promoError;
        } else {
            $form['qty'] = round($form['qty'], 1);
            if (!$sedangEdit) {
                $orders[] = [
                    'id' => uniqid('ORD'), 'username' => $form['nama_pelanggan'],
                    'layanan' => $layananDipilih['nama'], 'harga' => $layananDipilih['harga'],
                    'satuan' => $layananDipilih['satuan'], 'qty' => $form['qty'],
                    'catatan' => $form['catatan'], 'status' => $form['status'],
                    'tanggal' => tglPendek(null, true), 'diskon' => $diskon, 'promo' => $namaPromo,
                ];
                setFlash('sukses', 'Transaksi pelanggan kios berhasil dicatat.');
            } else {
                foreach ($orders as $i => $o) {
                    if ($o['id'] === $form['id']) {
                        $orders[$i]['layanan'] = $layananDipilih['nama'];
                        $orders[$i]['harga'] = $layananDipilih['harga'];
                        $orders[$i]['satuan'] = $layananDipilih['satuan'];
                        $orders[$i]['qty'] = $form['qty'];
                        $orders[$i]['catatan'] = $form['catatan'];
                        $orders[$i]['status'] = $form['status'];
                    }
                }
                setFlash('sukses', $masihMenunggu ? 'Berat tersimpan. Pesanan sekarang berstatus Diterima.' : 'Transaksi berhasil diubah.');
            }
            saveOrders($orders);
            redirect('transaksi.php');
        }
    }

    // ----- DELETE -----
    if ($aksi === 'hapus') {
        $id = $_POST['id'] ?? '';
        $sisa = [];
        foreach ($orders as $o) {
            if ($o['id'] !== $id) {
                $sisa[] = $o;
            }
        }
        saveOrders($sisa);
        setFlash('sukses', 'Transaksi berhasil dihapus.');
        redirect('transaksi.php');
    }
}

$users = loadUsers();
$layananList = loadLayanan();
$promoAktif = promoAktifList();
$namaPromoTetap = '';
$editMenunggu = false; // true kalau yang diedit adalah pesanan aplikasi yang belum ditimbang

// mode edit: cari transaksi, lalu cocokkan layanannya (disimpan berupa nama) ke ID layanan
if (isset($_GET['edit']) && $error === '') {
    foreach (loadOrders() as $o) {
        if ($o['id'] === $_GET['edit']) {
            $idLayanan = '';
            foreach ($layananList as $l) {
                if ($l['nama'] === $o['layanan']) {
                    $idLayanan = $l['id'];
                }
            }
            $form = [
                'id' => $o['id'], 'nama_pelanggan' => namaPengguna($o['username'], $users),
                'layanan' => $idLayanan, 'qty' => $o['qty'] > 0 ? $o['qty'] : '',
                'status' => $o['status'], 'catatan' => $o['catatan'], 'promo' => '',
            ];
        }
    }
}
// saat edit (termasuk saat gagal simpan), ambil diskon tetap dan status asli dari data
if ($form['id'] !== '') {
    foreach (loadOrders() as $o) {
        if ($o['id'] === $form['id']) {
            $diskonTetap = $o['diskon'];
            $namaPromoTetap = $o['promo'];
            $editMenunggu = ($o['status'] === 'Menunggu Jemput');
        }
    }
}
$modeEdit = ($form['id'] !== '');

$semua = array_reverse(loadOrders());
$totalPendapatan = 0;
$jumlahMenunggu = 0;
foreach ($semua as $o) {
    $totalPendapatan += totalOrder($o);
    if ($o['status'] === 'Menunggu Jemput') {
        $jumlahMenunggu++;
    }
}

$judulHalaman = 'Transaksi';
$subJudul = 'Catat pelanggan kios dan pantau semua transaksi. Klik No. Nota untuk melihat atau mencetak nota.';
$jsTambahan = ['estimasi.js', 'transaksi.js'];
require 'includes/header.php';
?>

<div class="stat-grid dua">
  <div class="stat-card">
    <div class="stat-ikon biru"><?= ikon('file', 22) ?></div>
    <div><span class="stat-label">Total transaksi</span><span class="stat-value"><?= count($semua) ?></span></div>
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
    <input type="hidden" name="aksi" value="simpan">
    <input type="hidden" name="id" value="<?= e($form['id']) ?>">

    <div class="form-grid">
      <div class="field">
        <label for="nama_pelanggan">Nama pelanggan</label>
        <input type="text" id="nama_pelanggan" name="nama_pelanggan" value="<?= e($form['nama_pelanggan']) ?>"
               placeholder="Nama pelanggan" <?= $modeEdit ? 'readonly' : 'required' ?>>
      </div>
      <div class="field">
        <label for="layanan">Layanan</label>
        <select id="layanan" name="layanan" required>
          <option value="" data-harga="0">-- Pilih layanan --</option>
          <?php foreach ($layananList as $l): ?>
            <option value="<?= e($l['id']) ?>" data-harga="<?= (int)$l['harga'] ?>" <?= $form['layanan'] === $l['id'] ? 'selected' : '' ?>>
              <?= e($l['nama']) ?> - <?= e(rupiah($l['harga'])) ?> / <?= e($l['satuan']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="qty">Berat / jumlah (kg atau item)</label>
        <input type="number" id="qty" name="qty" min="0.1" max="500" step="0.1" placeholder="Hasil timbangan"
               value="<?= $form['qty'] !== '' ? e($form['qty']) : '' ?>" required>
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
            <option value="<?= e($p['id']) ?>" data-diskon="<?= (int)$p['diskon'] ?>" <?= $form['promo'] === $p['id'] ? 'selected' : '' ?>>
              <?= e($p['judul']) ?> (<?= (int)$p['diskon'] ?>%)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>

      <div class="field penuh">
        <label for="catatan"><?= $editMenunggu ? 'Alamat jemput / catatan' : 'Catatan (boleh dikosongkan)' ?></label>
        <input type="text" id="catatan" name="catatan" value="<?= e($form['catatan']) ?>">
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
    <input type="search" class="cari" placeholder="Cari nota, nama, layanan..." data-cari="tabelTransaksi">
  </div>

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
          <tr data-kosong><td colspan="9" class="kosong">Belum ada transaksi.</td></tr>
        <?php endif; ?>
        <?php foreach ($semua as $o): $asal = sumberOrder($o, $users); ?>
          <tr data-asal="<?= strtolower($asal) ?>" data-status="<?= $o['status'] === 'Menunggu Jemput' ? 'menunggu' : 'proses' ?>">
            <td><a href="nota.php?id=<?= e($o['id']) ?>" class="kode"><?= e($o['id']) ?></a></td>
            <td><?= e(namaPengguna($o['username'], $users)) ?></td>
            <td><?= e($o['layanan']) ?></td>
            <td><?= beratLabel($o) ?></td>
            <td class="uang"><?= labelTotal($o) ?></td>
            <td><span class="asal asal-<?= strtolower($asal) ?>"><?= e($asal) ?></span></td>
            <td><?= e(implode(" ", array_slice(explode(" ", $o["tanggal"]), 0, 3))) ?></td>
            <td><?= badgeStatus($o['status']) ?></td>
            <td class="aksi">
              <a href="transaksi.php?edit=<?= e($o['id']) ?>#formTransaksi" class="btn btn-sm <?= $o['status'] === 'Menunggu Jemput' ? 'btn-primary' : 'btn-ghost' ?>">
                <?= $o['status'] === 'Menunggu Jemput' ? 'Isi berat' : 'Edit' ?>
              </a>
              <?= formHapus($o['id'], 'Yakin hapus transaksi ini?') ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require 'includes/footer.php'; ?>
