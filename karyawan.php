<?php
// ============================================================
// karyawan.php  -  CRUD Karyawan (khusus admin)
// ============================================================
require_once 'includes/config.php';
wajibLogin('admin');

$error = '';
$form = ['id' => '', 'nama' => '', 'jabatan' => '', 'no_hp' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $karyawan = loadKaryawan();

    // ----- CREATE & UPDATE -----
    if ($aksi === 'simpan') {
        $form['id'] = $_POST['id'] ?? '';
        $form['nama'] = bersihkan($_POST['nama'] ?? '');
        $form['jabatan'] = bersihkan($_POST['jabatan'] ?? '');
        $form['no_hp'] = bersihkan($_POST['no_hp'] ?? '');

        if ($form['nama'] === '' || $form['jabatan'] === '' || $form['no_hp'] === '') {
            $error = 'Nama, jabatan, dan No. HP wajib diisi ya.';
        } elseif (!preg_match('/^\+?[0-9]{9,15}$/', $form['no_hp'])) {
            $error = 'No. HP harus berupa angka (9-15 digit).';
        } else {
            if ($form['id'] === '') {
                $karyawan[] = [
                    'id' => uniqid('KRY'), 'nama' => $form['nama'],
                    'jabatan' => $form['jabatan'], 'no_hp' => $form['no_hp'],
                ];
                setFlash('sukses', 'Karyawan baru berhasil ditambahkan.');
            } else {
                foreach ($karyawan as $i => $k) {
                    if ($k['id'] === $form['id']) {
                        $karyawan[$i]['nama'] = $form['nama'];
                        $karyawan[$i]['jabatan'] = $form['jabatan'];
                        $karyawan[$i]['no_hp'] = $form['no_hp'];
                    }
                }
                setFlash('sukses', 'Data karyawan berhasil diubah.');
            }
            saveKaryawan($karyawan);
            redirect('karyawan.php');
        }
    }

    // ----- DELETE -----
    if ($aksi === 'hapus') {
        $id = $_POST['id'] ?? '';
        $sisa = [];
        foreach ($karyawan as $k) {
            if ($k['id'] !== $id) {
                $sisa[] = $k;
            }
        }
        saveKaryawan($sisa);
        setFlash('sukses', 'Karyawan berhasil dihapus.');
        redirect('karyawan.php');
    }
}

if (isset($_GET['edit']) && $error === '') {
    foreach (loadKaryawan() as $k) {
        if ($k['id'] === $_GET['edit']) {
            $form = $k;
        }
    }
}
$modeEdit = ($form['id'] !== '');
$daftar = loadKaryawan();

$judulHalaman = 'Manajemen Karyawan';
$subJudul = 'Kelola data karyawan yang bekerja di GOLaundry.';
require 'includes/header.php';
?>

<div class="dua-kolom">
  <section class="card">
    <h3><?= $modeEdit ? 'Edit Karyawan' : 'Tambah Karyawan' ?></h3>

    <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="karyawan.php" class="form">
      <input type="hidden" name="aksi" value="simpan">
      <input type="hidden" name="id" value="<?= e($form['id']) ?>">

      <div class="field">
        <label for="nama">Nama Karyawan</label>
        <input type="text" id="nama" name="nama" value="<?= e($form['nama']) ?>" required>
      </div>
      <div class="field">
        <label for="jabatan">Jabatan</label>
        <input type="text" id="jabatan" name="jabatan" placeholder="Contoh: Kasir, Kurir" value="<?= e($form['jabatan']) ?>" required>
      </div>
      <div class="field">
        <label for="no_hp">No. HP</label>
        <input type="text" id="no_hp" name="no_hp" placeholder="08123456789" value="<?= e($form['no_hp']) ?>" required>
      </div>

      <div class="form-aksi">
        <button type="submit" class="btn btn-primary"><?= $modeEdit ? 'Simpan Perubahan' : 'Tambah Karyawan' ?></button>
        <?php if ($modeEdit): ?><a href="karyawan.php" class="btn btn-ghost">Batal</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section class="card">
    <div class="card-kepala">
      <h3>Daftar Karyawan <span class="jumlah"><?= count($daftar) ?></span></h3>
      <input type="search" class="cari" placeholder="Cari karyawan..." data-cari="tabelKaryawan">
    </div>
    <div class="tabel-wrap">
      <table id="tabelKaryawan">
        <thead><tr><th>Nama</th><th>Jabatan</th><th>No. HP</th><th class="kanan">Aksi</th></tr></thead>
        <tbody>
          <?php if (count($daftar) === 0): ?>
            <tr data-kosong><td colspan="4" class="kosong">Belum ada karyawan.</td></tr>
          <?php endif; ?>
          <?php foreach ($daftar as $k): ?>
            <tr>
              <td><strong><?= e($k['nama']) ?></strong></td>
              <td><span class="chip"><?= e($k['jabatan']) ?></span></td>
              <td><?= e($k['no_hp']) ?></td>
              <td class="aksi">
                <a href="karyawan.php?edit=<?= e($k['id']) ?>" class="btn btn-sm btn-ghost">Edit</a>
                <?= formHapus($k['id'], 'Yakin hapus karyawan ' . $k['nama'] . '?') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php require 'includes/footer.php'; ?>
