<?php

require_once 'includes/config.php';
wajibLogin('admin');

$error = '';
$form = ['username' => '', 'nama' => '', 'edit' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'simpan') {
        $form['edit'] = ($_POST['mode'] ?? '') === 'edit';
        $form['username'] = normalUser($_POST['username'] ?? '');
        $form['nama'] = bersihkan($_POST['nama'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($form['nama'] === '' || $form['username'] === '') {
            $error = 'Nama dan email/No. HP wajib diisi ya.';
        } elseif ($form['edit']) {
            $akun = cariUser($form['username']);
            if ($akun === false || $akun['role'] !== 'customer') {
                $error = 'Pelanggan tidak ditemukan.';
            } elseif ($password !== '' && strlen($password) < 6) {
                $error = 'Password baru minimal 6 karakter.';
            } else {
                ubahUser($form['username'], $form['nama'], $password);
                setFlash('sukses', 'Data pelanggan berhasil diubah.');
                redirect('pelanggan.php');
            }
        } else {
            if (!usernameValid($form['username'])) {
                $error = 'Isi dengan email yang valid atau nomor HP (9-15 angka).';
            } elseif (strlen($password) < 6) {
                $error = 'Password minimal 6 karakter.';
            } elseif (cariUser($form['username']) !== false
                      || !tambahUser($form['username'], $password, $form['nama'], 'customer')) {
                $error = 'Email/No. HP ini sudah terdaftar.';
            } else {
                setFlash('sukses', 'Pelanggan baru berhasil ditambahkan.');
                redirect('pelanggan.php');
            }
        }
    }

    if ($aksi === 'hapus') {
        $username = $_POST['username'] ?? '';
        if (is_string($username) && hapusPelanggan($username) > 0) {
            setFlash('sukses', 'Akun pelanggan berhasil dihapus.');
        }
        redirect('pelanggan.php');
    }
}

if (isset($_GET['edit']) && $error === '') {
    $u = is_string($_GET['edit']) ? cariUser($_GET['edit']) : false;
    if ($u !== false && $u['role'] === 'customer') {
        $form = ['username' => $u['username'], 'nama' => $u['nama'], 'edit' => true];
    }
}

$daftar = daftarPelanggan();

$judulHalaman = 'Manajemen Pelanggan';
$subJudul = 'Kelola akun pelanggan yang terdaftar di GOLaundry.';
require 'includes/header.php';
?>

<div class="dua-kolom">
  <section class="card">
    <h3><?= $form['edit'] ? 'Edit Pelanggan' : 'Tambah Pelanggan' ?></h3>

    <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="pelanggan.php" class="form">
      <input type="hidden" name="aksi" value="simpan">
      <input type="hidden" name="mode" value="<?= $form['edit'] ? 'edit' : 'tambah' ?>">

      <div class="field">
        <label for="nama">Nama Lengkap</label>
        <input type="text" id="nama" name="nama" value="<?= e($form['nama']) ?>" required>
      </div>
      <div class="field">
        <label for="username">Email atau No. HP</label>
        <input type="text" id="username" name="username" value="<?= e($form['username']) ?>"
               <?= $form['edit'] ? 'readonly' : '' ?> required>
      </div>
      <div class="field">
        <label for="password"><?= $form['edit'] ? 'Password Baru (kosongkan jika tidak diganti)' : 'Password' ?></label>
        <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" <?= $form['edit'] ? '' : 'required' ?>>
      </div>

      <div class="form-aksi">
        <button type="submit" class="btn btn-primary"><?= $form['edit'] ? 'Simpan Perubahan' : 'Tambah Pelanggan' ?></button>
        <?php if ($form['edit']): ?><a href="pelanggan.php" class="btn btn-ghost">Batal</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section class="card">
    <div class="card-kepala">
      <h3>Daftar Pelanggan <span class="jumlah"><?= count($daftar) ?></span></h3>
      <input type="search" class="cari" placeholder="Cari pelanggan..." data-cari="tabelPelanggan">
    </div>
    <div class="tabel-wrap">
      <table id="tabelPelanggan">
        <thead><tr><th>Nama</th><th>Email / No. HP</th><th class="kanan">Aksi</th></tr></thead>
        <tbody>
          <?php if (count($daftar) === 0): ?>
            <tr data-kosong><td colspan="3" class="kosong">Belum ada pelanggan.</td></tr>
          <?php endif; ?>
          <?php foreach ($daftar as $p): ?>
            <tr>
              <td><strong><?= e($p['nama']) ?></strong></td>
              <td><?= e($p['username']) ?></td>
              <td class="aksi">
                <a href="pelanggan.php?edit=<?= urlencode($p['username']) ?>" class="btn btn-sm btn-ghost">Edit</a>
                <?= formHapus($p['username'], 'Yakin hapus akun ' . $p['nama'] . '?', 'username') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php require 'includes/footer.php'; ?>