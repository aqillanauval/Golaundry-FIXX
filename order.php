<?php
// ============================================================
// order.php  -  CRUD Pesanan milik pelanggan (+ pakai promo)
//
// ALUR PESANAN LEWAT APLIKASI:
//   1. Pelanggan memilih layanan (berat belum diketahui, jadi TIDAK diisi di sini)
//   2. Pesanan tersimpan dengan status "Menunggu Jemput" dan berat 0
//   3. Staff / kurir mengambil cucian, menimbang, lalu mengisi berat
//      di dashboard admin -> status berubah jadi "Diterima"
//   4. Di dashboard pelanggan muncul berat, harga, dan total setelah promo
//
// Create : buat pesanan baru
// Read   : tabel "Pesanan Saya"
// Update : edit pesanan (hanya jika status masih "Menunggu Jemput")
// Delete : batalkan pesanan (hanya jika status masih "Menunggu Jemput")
// Promo  : pelanggan boleh memilih 1 promo yang masih berlaku.
//          Besar diskon SELALU diambil dari data promo di server,
//          bukan dari kiriman browser (supaya tidak bisa dicurangi).
// ============================================================
require_once 'includes/config.php';
wajibLogin('customer');

$username = $_SESSION['user'];
$error = '';
$form = ['id' => '', 'layanan' => '', 'catatan' => '', 'promo' => ''];

// cari pesanan milik user ini berdasarkan id (dipakai saat edit)
function cariPesananSaya($id, $username)
{
    foreach (loadOrders() as $o) {
        if ($o['id'] === $id && $o['username'] === $username) {
            return $o;
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $orders = loadOrders();

    // ----- CREATE & UPDATE -----
    if ($aksi === 'simpan') {
        $form['id'] = $_POST['id'] ?? '';
        $form['layanan'] = $_POST['layanan'] ?? '';
        $form['catatan'] = bersihkan($_POST['catatan'] ?? '');
        $form['promo'] = $_POST['promo'] ?? '';

        $layananDipilih = cariLayanan($form['layanan']);
        $orderLama = ($form['id'] !== '') ? cariPesananSaya($form['id'], $username) : null;

        // tentukan diskon dari promo yang dipilih
        $diskon = 0;
        $namaPromo = '';
        $promoError = '';
        if ($form['promo'] === 'lama') {
            // pelanggan tetap memakai promo lama dari pesanan ini (promonya mungkin sudah berakhir)
            if ($orderLama) {
                $diskon = $orderLama['diskon'];
                $namaPromo = $orderLama['promo'];
            } else {
                $promoError = 'Promo tidak valid.';
            }
        } elseif ($form['promo'] !== '') {
            $p = cariPromo($form['promo']);
            if (!$p || !promoMasihBerlaku($p)) {
                $promoError = 'Promo tidak valid atau sudah berakhir.';
            } else {
                $diskon = $p['diskon'];
                $namaPromo = $p['judul'];
            }
        }

        if (!$layananDipilih) {
            $error = 'Silakan pilih layanan dulu ya.';
        } elseif ($form['catatan'] === '') {
            $error = 'Isi alamat jemput dulu ya, supaya kurir bisa menemukanmu.';
        } elseif ($promoError !== '') {
            $error = $promoError;
        } elseif ($form['id'] === '') {
            // CREATE: berat belum diketahui (0), diisi staff setelah cucian diambil
            $orders[] = [
                'id' => uniqid('ORD'), 'username' => $username,
                'layanan' => $layananDipilih['nama'], 'harga' => $layananDipilih['harga'],
                'satuan' => $layananDipilih['satuan'], 'qty' => 0,
                'catatan' => $form['catatan'], 'status' => 'Menunggu Jemput', 'tanggal' => tglPendek(null, true),
                'diskon' => $diskon, 'promo' => $namaPromo,
            ];
            saveOrders($orders);
            setFlash('sukses', 'Pesanan berhasil dibuat. Kurir akan menjemput cucianmu dan menimbangnya.');
            redirect('dashboard-customer.php');
        } else {
            // UPDATE: hanya pesanan milik sendiri yang masih Menunggu Jemput
            $berhasil = false;
            foreach ($orders as $i => $o) {
                if ($o['id'] === $form['id'] && $o['username'] === $username && $o['status'] === 'Menunggu Jemput') {
                    $orders[$i]['layanan'] = $layananDipilih['nama'];
                    $orders[$i]['harga'] = $layananDipilih['harga'];
                    $orders[$i]['satuan'] = $layananDipilih['satuan'];
                    $orders[$i]['catatan'] = $form['catatan'];
                    $orders[$i]['diskon'] = $diskon;
                    $orders[$i]['promo'] = $namaPromo;
                    $berhasil = true;
                }
            }
            if ($berhasil) {
                saveOrders($orders);
                setFlash('sukses', 'Pesanan berhasil diubah.');
                redirect('order.php');
            } else {
                $error = 'Pesanan tidak ditemukan atau sudah dijemput.';
            }
        }
    }

    // ----- DELETE (batalkan pesanan) -----
    if ($aksi === 'hapus') {
        $id = $_POST['id'] ?? '';
        $sisa = [];
        $dibatalkan = false;
        foreach ($orders as $o) {
            if ($o['id'] === $id && $o['username'] === $username && $o['status'] === 'Menunggu Jemput') {
                $dibatalkan = true; // pesanan ini tidak dimasukkan lagi = terhapus
            } else {
                $sisa[] = $o;
            }
        }
        saveOrders($sisa);
        if ($dibatalkan) {
            setFlash('sukses', 'Pesanan berhasil dibatalkan.');
        } else {
            setFlash('error', 'Pesanan tidak bisa dibatalkan karena cucianmu sudah dijemput.');
        }
        redirect('order.php');
    }
}

$layananList = loadLayanan();
$promoAktif = promoAktifList();

$pesananSaya = [];
foreach (loadOrders() as $o) {
    if ($o['username'] === $username) {
        $pesananSaya[] = $o;
    }
}
$pesananSaya = array_reverse($pesananSaya);

$promoLama = null; // promo dari pesanan yang sedang diedit, kalau sudah tidak ada di daftar aktif

// mode edit: isi form dengan data pesanan yang dipilih
if (isset($_GET['edit']) && $error === '') {
    foreach ($pesananSaya as $o) {
        if ($o['id'] === $_GET['edit'] && $o['status'] === 'Menunggu Jemput') {
            $idLayanan = '';
            foreach ($layananList as $l) {
                if ($l['nama'] === $o['layanan']) {
                    $idLayanan = $l['id'];
                }
            }
            $pilihPromo = '';
            if ($o['diskon'] > 0) {
                $pilihPromo = 'lama';
                foreach ($promoAktif as $p) {
                    if ($p['judul'] === $o['promo'] && $p['diskon'] === $o['diskon']) {
                        $pilihPromo = $p['id']; // promonya masih aktif
                    }
                }
            }
            $form = ['id' => $o['id'], 'layanan' => $idLayanan, 'catatan' => $o['catatan'], 'promo' => $pilihPromo];
            if ($pilihPromo === 'lama') {
                $promoLama = ['judul' => $o['promo'], 'diskon' => $o['diskon']];
            }
        }
    }
}

// datang dari tombol "Pakai promo ini" di dashboard -> promo langsung terpilih
if ($form['id'] === '' && $form['promo'] === '' && isset($_GET['promo'])) {
    $p = cariPromo($_GET['promo']);
    if ($p && promoMasihBerlaku($p)) {
        $form['promo'] = $p['id'];
    }
}

// saat gagal simpan di mode edit, promo lama tetap bisa dipilih
if ($form['promo'] === 'lama' && $promoLama === null && $form['id'] !== '') {
    $ol = cariPesananSaya($form['id'], $username);
    if ($ol) {
        $promoLama = ['judul' => $ol['promo'], 'diskon' => $ol['diskon']];
    }
}

$modeEdit = ($form['id'] !== '');

$judulHalaman = $modeEdit ? 'Edit pesanan' : 'Buat pesanan';
$subJudul = 'Pilih layanannya saja. Berat cucian ditimbang kurir saat menjemput.';
$jsTambahan = ['order.js'];
require 'includes/header.php';
?>

<?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<form method="post" action="order.php" class="order-grid" id="formPesan">
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
            <input type="radio" name="layanan" value="<?= e($l['id']) ?>"
                   data-nama="<?= e($l['nama']) ?>" data-harga="<?= (int)$l['harga'] ?>" data-satuan="<?= e($l['satuan']) ?>"
                   <?= $form['layanan'] === $l['id'] ? 'checked' : '' ?>>
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
        <textarea id="catatan" name="catatan" rows="3" required placeholder="Contoh: Kos Melati no. 5, dekat gerbang kampus. Pisahkan baju putih."><?= e($form['catatan']) ?></textarea>
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
            <input type="radio" name="promo" value="<?= e($p['id']) ?>" data-diskon="<?= (int)$p['diskon'] ?>" data-nama="<?= e($p['judul']) ?>"
                   <?= $form['promo'] === $p['id'] ? 'checked' : '' ?>>
            <span class="isi isi-promo">
              <span class="lencana"><?= (int)$p['diskon'] ?>%</span>
              <span class="teks"><strong><?= e($p['judul']) ?></strong><small>Berlaku sampai <?= e(tglPendek(strtotime($p['sampai']))) ?></small></span>
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
            <td><?= e($o['tanggal']) ?></td>
            <td><?= e($o['layanan']) ?></td>
            <td><?= beratLabel($o) ?></td>
            <td class="uang"><?= labelTotal($o) ?></td>
            <td><?= badgeStatus($o['status']) ?></td>
            <td class="aksi">
              <?php if ($o['status'] === 'Menunggu Jemput'): ?>
                <a href="order.php?edit=<?= e($o['id']) ?>" class="btn btn-sm btn-ghost">Edit</a>
                <form method="post" class="inline" data-konfirmasi="Yakin batalkan pesanan ini?">
                  <input type="hidden" name="aksi" value="hapus">
                  <input type="hidden" name="id" value="<?= e($o['id']) ?>">
                  <button type="submit" class="btn btn-sm btn-danger">Batalkan</button>
                </form>
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

<?php require 'includes/footer.php'; ?>
