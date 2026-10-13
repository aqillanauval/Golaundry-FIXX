<?php
// ============================================================
// riwayat.php  -  Riwayat transaksi pelanggan + CRUD Ulasan
// Pesanan yang statusnya sudah Selesai / Diambil bisa diberi ulasan:
//   Create : "Beri Ulasan"      Update : "Edit"      Delete : "Hapus"
// ============================================================
require_once 'includes/config.php';
wajibLogin('customer');

$username = $_SESSION['user'];
$error = '';
$formUlasan = null; // kalau tidak null, kartu form ulasan ditampilkan

// pesanan boleh diulas kalau milik sendiri & sudah selesai dikerjakan
function bolehDiulas($idPesanan, $username)
{
    foreach (loadOrders() as $o) {
        if ($o['id'] === $idPesanan && $o['username'] === $username
            && ($o['status'] === 'Selesai' || $o['status'] === 'Diambil')) {
            return true;
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $id = $_POST['id'] ?? '';
    $ulasan = loadUlasan();

    if (!bolehDiulas($id, $username)) {
        setFlash('error', 'Pesanan tidak ditemukan atau belum selesai.');
        redirect('riwayat.php');
    }

    // ----- CREATE & UPDATE ulasan (caranya sama: simpan / timpa) -----
    if ($aksi === 'simpan_ulasan') {
        $rating = (int)($_POST['rating'] ?? 0);
        $komentar = bersihkan($_POST['komentar'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $error = 'Pilih rating 1 sampai 5 bintang ya.';
        } elseif ($komentar === '') {
            $error = 'Komentar wajib diisi.';
        } else {
            $ulasan[$id] = ['rating' => $rating, 'komentar' => $komentar];
            saveUlasan($ulasan);
            setFlash('sukses', 'Terima kasih! Ulasanmu sudah tersimpan.');
            redirect('riwayat.php');
        }
        $formUlasan = ['id' => $id, 'rating' => $rating, 'komentar' => $komentar];
    }

    // ----- DELETE ulasan -----
    if ($aksi === 'hapus') {
        unset($ulasan[$id]);
        saveUlasan($ulasan);
        setFlash('sukses', 'Ulasan berhasil dihapus.');
        redirect('riwayat.php');
    }
}

$ulasan = loadUlasan();

// klik "Beri Ulasan" / "Edit" -> riwayat.php?ulas=ID
if ($formUlasan === null && isset($_GET['ulas']) && bolehDiulas($_GET['ulas'], $username)) {
    $idUlas = $_GET['ulas'];
    $formUlasan = [
        'id' => $idUlas,
        'rating' => isset($ulasan[$idUlas]) ? $ulasan[$idUlas]['rating'] : 5,
        'komentar' => isset($ulasan[$idUlas]) ? $ulasan[$idUlas]['komentar'] : '',
    ];
}

$pesananSaya = [];
foreach (loadOrders() as $o) {
    if ($o['username'] === $username) {
        $pesananSaya[] = $o;
    }
}
$pesananSaya = array_reverse($pesananSaya);

$judulHalaman = 'Riwayat Transaksi';
$subJudul = 'Semua pesanan laundry kamu. Klik No. Nota untuk lihat / cetak nota.';
$jsTambahan = ['riwayat.js'];
require 'includes/header.php';
?>

<?php if ($formUlasan !== null): ?>
<section class="card sorot" id="formUlasan">
  <h3>Ulasan untuk Pesanan <?= e($formUlasan['id']) ?></h3>

  <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="riwayat.php" class="form">
    <input type="hidden" name="aksi" value="simpan_ulasan">
    <input type="hidden" name="id" value="<?= e($formUlasan['id']) ?>">

    <div class="field">
      <label>Rating</label>
      <div class="bintang-input">
        <?php for ($b = 5; $b >= 1; $b--): ?>
          <input type="radio" name="rating" id="bintang<?= $b ?>" value="<?= $b ?>" <?= (int)$formUlasan['rating'] === $b ? 'checked' : '' ?>>
          <label for="bintang<?= $b ?>" title="<?= $b ?> bintang">&#9733;</label>
        <?php endfor; ?>
      </div>
    </div>
    <div class="field">
      <label for="komentar">Komentar</label>
      <textarea id="komentar" name="komentar" rows="3" placeholder="Ceritakan pengalamanmu" required><?= e($formUlasan['komentar']) ?></textarea>
    </div>

    <div class="form-aksi">
      <button type="submit" class="btn btn-primary">Simpan Ulasan</button>
      <a href="riwayat.php" class="btn btn-ghost">Batal</a>
    </div>
  </form>
</section>
<?php endif; ?>

<section class="card">
  <div class="card-kepala">
    <h3>Semua Pesanan <span class="jumlah"><?= count($pesananSaya) ?></span></h3>
    <input type="search" class="cari" placeholder="Cari nota atau layanan..." data-cari="tabelRiwayat">
  </div>
  <div class="tabel-wrap">
    <table id="tabelRiwayat">
      <thead><tr><th>No. Nota</th><th>Tanggal</th><th>Layanan</th><th>Berat</th><th>Total</th><th>Status</th><th>Ulasan</th></tr></thead>
      <tbody>
        <?php if (count($pesananSaya) === 0): ?>
          <tr data-kosong><td colspan="7" class="kosong">Belum ada transaksi.</td></tr>
        <?php endif; ?>
        <?php foreach ($pesananSaya as $o): ?>
          <tr>
            <td><a href="nota.php?id=<?= e($o['id']) ?>" class="kode"><?= e($o['id']) ?></a></td>
            <td><?= e($o['tanggal']) ?></td>
            <td><?= e($o['layanan']) ?></td>
            <td><?= beratLabel($o) ?></td>
            <td class="uang"><?= labelTotal($o) ?></td>
            <td><?= badgeStatus($o['status']) ?></td>
            <td>
              <?php if ($o['status'] === 'Selesai' || $o['status'] === 'Diambil'): ?>
                <?php if (isset($ulasan[$o['id']])): $u = $ulasan[$o['id']]; ?>
                  <div class="bintang"><?= str_repeat('&#9733;', $u['rating']) ?><span><?= str_repeat('&#9733;', 5 - $u['rating']) ?></span></div>
                  <div class="komentar"><?= e($u['komentar']) ?></div>
                  <div class="aksi">
                    <a href="riwayat.php?ulas=<?= e($o['id']) ?>" class="btn btn-sm btn-ghost">Edit</a>
                    <?= formHapus($o['id'], 'Yakin hapus ulasan ini?') ?>
                  </div>
                <?php else: ?>
                  <a href="riwayat.php?ulas=<?= e($o['id']) ?>" class="btn btn-sm btn-primary">Beri Ulasan</a>
                <?php endif; ?>
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
