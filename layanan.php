<?php
// ============================================================
// layanan.php  -  CRUD Layanan (khusus admin)
// Create : form "Tambah Layanan"
// Read   : tabel daftar layanan
// Update : klik Edit -> form terisi (?edit=ID) -> Simpan
// Delete : tombol Hapus
// ============================================================
require_once 'includes/config.php';
wajibLogin('admin');

$error = '';
$form = ['id' => '', 'nama' => '', 'harga' => '', 'satuan' => 'kg']; // isi form (kosong = mode tambah)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $layanan = loadLayanan();

    // ----- CREATE & UPDATE -----
    if ($aksi === 'simpan') {
        $form['id'] = $_POST['id'] ?? '';
        $form['nama'] = bersihkan($_POST['nama'] ?? '');
        $form['harga'] = (int)($_POST['harga'] ?? 0);
        $form['satuan'] = bersihkan($_POST['satuan'] ?? '');

        if ($form['nama'] === '' || $form['harga'] <= 0 || $form['satuan'] === '') {
            $error = 'Nama, harga, dan satuan wajib diisi dengan benar ya.';
        } else {
            if ($form['id'] === '') {
                $layanan[] = [
                    'id' => uniqid('LYN'), 'nama' => $form['nama'],
                    'harga' => $form['harga'], 'satuan' => $form['satuan'],
                ];
                setFlash('sukses', 'Layanan baru berhasil ditambahkan.');
            } else {
                foreach ($layanan as $i => $l) {
                    if ($l['id'] === $form['id']) {
                        $layanan[$i]['nama'] = $form['nama'];
                        $layanan[$i]['harga'] = $form['harga'];
                        $layanan[$i]['satuan'] = $form['satuan'];
                    }
                }
                setFlash('sukses', 'Layanan berhasil diubah.');
            }
            saveLayanan($layanan);
            redirect('layanan.php');
        }
    }

    // ----- DELETE -----
    if ($aksi === 'hapus') {
        $id = $_POST['id'] ?? '';
        $sisa = [];
        foreach ($layanan as $l) {
            if ($l['id'] !== $id) {
                $sisa[] = $l;
            }
        }
        saveLayanan($sisa);
        setFlash('sukses', 'Layanan berhasil dihapus.');
        redirect('layanan.php');
    }
}

// mode edit: isi form dengan data yang dipilih
if (isset($_GET['edit']) && $error === '') {
    $dipilih = cariLayanan($_GET['edit']);
    if ($dipilih) {
        $form = $dipilih;
    }
}
$modeEdit = ($form['id'] !== '');

$daftar = loadLayanan();

$judulHalaman = 'Manajemen Layanan';
$subJudul = 'Atur daftar layanan dan harganya. Ini yang muncul di form pesanan pelanggan.';
require 'includes/header.php';
?>

<div class="dua-kolom">
  <section class="card">
    <h3><?= $modeEdit ? 'Edit Layanan' : 'Tambah Layanan' ?></h3>

    <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="layanan.php" class="form">
      <input type="hidden" name="aksi" value="simpan">
      <input type="hidden" name="id" value="<?= e($form['id']) ?>">

      <div class="field">
        <label for="nama">Nama Layanan</label>
        <input type="text" id="nama" name="nama" placeholder="Contoh: Cuci Setrika" value="<?= e($form['nama']) ?>" required>
      </div>
      <div class="field">
        <label for="harga">Harga (Rp)</label>
        <input type="number" id="harga" name="harga" min="1" placeholder="10000" value="<?= $form['harga'] ? e($form['harga']) : '' ?>" required>
      </div>
      <div class="field">
        <label for="satuan">Satuan</label>
        <input type="text" id="satuan" name="satuan" list="pilihanSatuan" placeholder="kg / item" value="<?= e($form['satuan']) ?>" required>
        <datalist id="pilihanSatuan"><option value="kg"><option value="item"><option value="pcs"></datalist>
      </div>

      <div class="form-aksi">
        <button type="submit" class="btn btn-primary"><?= $modeEdit ? 'Simpan Perubahan' : 'Tambah Layanan' ?></button>
        <?php if ($modeEdit): ?><a href="layanan.php" class="btn btn-ghost">Batal</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section class="card">
    <div class="card-kepala">
      <h3>Daftar Layanan <span class="jumlah"><?= count($daftar) ?></span></h3>
      <input type="search" class="cari" placeholder="Cari layanan..." data-cari="tabelLayanan">
    </div>
    <div class="tabel-wrap">
      <table id="tabelLayanan">
        <thead><tr><th>Nama</th><th>Harga</th><th>Satuan</th><th class="kanan">Aksi</th></tr></thead>
        <tbody>
          <?php if (count($daftar) === 0): ?>
            <tr data-kosong><td colspan="4" class="kosong">Belum ada layanan.</td></tr>
          <?php endif; ?>
          <?php foreach ($daftar as $l): ?>
            <tr>
              <td><strong><?= e($l['nama']) ?></strong></td>
              <td><?= e(rupiah($l['harga'])) ?></td>
              <td>per <?= e($l['satuan']) ?></td>
              <td class="aksi">
                <a href="layanan.php?edit=<?= e($l['id']) ?>" class="btn btn-sm btn-ghost">Edit</a>
                <?= formHapus($l['id'], 'Yakin hapus layanan ' . $l['nama'] . '?') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php require 'includes/footer.php'; ?>
