<?php
// ============================================================
// promo.php  -  FITUR BARU: CRUD Promo + Upload Foto (khusus admin)
// Materi yang dipakai:
//   - Form & method POST (enctype="multipart/form-data" untuk upload)
//   - File Handling: file_put_contents / explode (lewat fungsi di fungsi.php)
//   - Upload: $_FILES, move_uploaded_file, validasi ekstensi & ukuran
//   - Validasi form di server, session (wajibLogin), flash message
// Promo yang masih berlaku akan tampil di dashboard pelanggan.
// ============================================================
require_once 'includes/config.php';
wajibLogin('admin');

$error = '';
$form = ['id' => '', 'judul' => '', 'deskripsi' => '', 'diskon' => '', 'sampai' => '', 'foto' => ''];

// fungsi kecil: proses upload foto. Mengembalikan nama file baru, atau teks error
function prosesUploadFoto($berkas)
{
    $namaAsli = $berkas['name'];
    $tmp = $berkas['tmp_name'];
    $kodeError = $berkas['error'];
    $ekstensiBoleh = ['jpg', 'jpeg', 'png'];
    $ukuranMaks = 2 * 1024 * 1024; // 2 MB

    $ekstensi = strtolower(pathinfo($namaAsli, PATHINFO_EXTENSION));

    if ($kodeError !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload foto gagal. Coba pilih file lain.'];
    }
    if (!in_array($ekstensi, $ekstensiBoleh)) {
        return ['error' => 'Foto harus berformat JPG atau PNG.'];
    }
    if ($berkas['size'] > $ukuranMaks) {
        return ['error' => 'Ukuran foto melebihi 2 MB.'];
    }
    if (@getimagesize($tmp) === false) {
        return ['error' => 'File yang diunggah bukan gambar yang valid.'];
    }

    // nama baru dibuat acak supaya tidak bentrok & tidak bisa ditebak
    $namaBaru = uniqid('promo_') . '.' . $ekstensi;
    if (!move_uploaded_file($tmp, FOLDER_UPLOAD . $namaBaru)) {
        return ['error' => 'Foto gagal disimpan di server.'];
    }
    return ['nama' => $namaBaru];
}

function hapusFotoPromo($namaFile)
{
    if ($namaFile !== '' && file_exists(FOLDER_UPLOAD . $namaFile)) {
        unlink(FOLDER_UPLOAD . $namaFile);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $promo = loadPromo();

    // ----- CREATE & UPDATE -----
    if ($aksi === 'simpan') {
        $form['id'] = $_POST['id'] ?? '';
        $form['judul'] = bersihkan($_POST['judul'] ?? '');
        $form['deskripsi'] = bersihkan($_POST['deskripsi'] ?? '');
        $form['diskon'] = (int)($_POST['diskon'] ?? 0);
        $form['sampai'] = $_POST['sampai'] ?? '';
        $form['foto'] = $_POST['foto_lama'] ?? '';

        $tanggalValid = (strtotime($form['sampai']) !== false) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $form['sampai']);

        if ($form['judul'] === '' || $form['deskripsi'] === '') {
            $error = 'Judul dan deskripsi promo wajib diisi.';
        } elseif ($form['diskon'] < 1 || $form['diskon'] > 100) {
            $error = 'Diskon harus antara 1 sampai 100 persen.';
        } elseif (!$tanggalValid) {
            $error = 'Tanggal berlaku sampai wajib diisi.';
        } else {
            $fotoBaru = '';
            // kalau user memilih file foto, proses uploadnya
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $hasil = prosesUploadFoto($_FILES['foto']);
                if (isset($hasil['error'])) {
                    $error = $hasil['error'];
                } else {
                    $fotoBaru = $hasil['nama'];
                }
            }

            if ($error === '') {
                if ($form['id'] === '') {
                    $promo[] = [
                        'id' => uniqid('PRM'), 'judul' => $form['judul'], 'deskripsi' => $form['deskripsi'],
                        'diskon' => $form['diskon'], 'sampai' => $form['sampai'], 'foto' => $fotoBaru,
                    ];
                    setFlash('sukses', 'Promo baru berhasil ditambahkan.');
                } else {
                    foreach ($promo as $i => $p) {
                        if ($p['id'] === $form['id']) {
                            if ($fotoBaru !== '') {
                                hapusFotoPromo($p['foto']); // foto lama dibuang, diganti yang baru
                                $promo[$i]['foto'] = $fotoBaru;
                            }
                            $promo[$i]['judul'] = $form['judul'];
                            $promo[$i]['deskripsi'] = $form['deskripsi'];
                            $promo[$i]['diskon'] = $form['diskon'];
                            $promo[$i]['sampai'] = $form['sampai'];
                        }
                    }
                    setFlash('sukses', 'Promo berhasil diubah.');
                }
                savePromo($promo);
                redirect('promo.php');
            }
        }
    }

    // ----- DELETE (file fotonya ikut dihapus) -----
    if ($aksi === 'hapus') {
        $id = $_POST['id'] ?? '';
        $sisa = [];
        foreach ($promo as $p) {
            if ($p['id'] === $id) {
                hapusFotoPromo($p['foto']);
            } else {
                $sisa[] = $p;
            }
        }
        savePromo($sisa);
        setFlash('sukses', 'Promo berhasil dihapus.');
        redirect('promo.php');
    }
}

// mode edit
if (isset($_GET['edit']) && $error === '') {
    foreach (loadPromo() as $p) {
        if ($p['id'] === $_GET['edit']) {
            $form = $p;
        }
    }
}
$modeEdit = ($form['id'] !== '');
$daftar = array_reverse(loadPromo());
$tampilAksi = true; // dipakai kartu_promo.php

$judulHalaman = 'Manajemen Promo';
$subJudul = 'Buat promo menarik. Pelanggan bisa langsung memakainya saat memesan.';
$jsTambahan = ['promo.js'];
require 'includes/header.php';
?>

<div class="dua-kolom">
  <section class="card">
    <h3><?= $modeEdit ? 'Edit Promo' : 'Tambah Promo' ?></h3>

    <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <!-- enctype="multipart/form-data" wajib supaya file foto ikut terkirim -->
    <form method="post" action="promo.php" class="form" enctype="multipart/form-data">
      <input type="hidden" name="aksi" value="simpan">
      <input type="hidden" name="id" value="<?= e($form['id']) ?>">
      <input type="hidden" name="foto_lama" value="<?= e($form['foto']) ?>">

      <div class="field">
        <label for="judul">Judul Promo</label>
        <input type="text" id="judul" name="judul" placeholder="Contoh: Diskon Mahasiswa" value="<?= e($form['judul']) ?>" required>
      </div>
      <div class="field">
        <label for="deskripsi">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" rows="3" placeholder="Jelaskan syarat dan keuntungan promo" required><?= e($form['deskripsi']) ?></textarea>
      </div>
      <div class="form-grid">
        <div class="field">
          <label for="diskon">Diskon (%)</label>
          <input type="number" id="diskon" name="diskon" min="1" max="100" value="<?= $form['diskon'] ? e($form['diskon']) : '' ?>" required>
        </div>
        <div class="field">
          <label for="sampai">Berlaku sampai</label>
          <input type="date" id="sampai" name="sampai" value="<?= e($form['sampai']) ?>" required>
        </div>
      </div>
      <div class="field">
        <label for="foto">Foto Banner (JPG/PNG, maks 2 MB)</label>
        <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png">
        <div class="preview-foto" id="previewFoto" <?= ($form['foto'] !== '' && file_exists(FOLDER_UPLOAD . $form['foto'])) ? '' : 'hidden' ?>>
          <img id="previewImg" alt="Pratinjau foto"
               src="<?= ($form['foto'] !== '') ? 'uploads/promo/' . e($form['foto']) : '' ?>">
          <small>Pratinjau foto<?= $modeEdit ? ' (kosongkan pilihan file kalau tidak ingin mengganti foto)' : '' ?></small>
        </div>
      </div>

      <div class="form-aksi">
        <button type="submit" class="btn btn-primary"><?= $modeEdit ? 'Simpan Perubahan' : 'Tambah Promo' ?></button>
        <?php if ($modeEdit): ?><a href="promo.php" class="btn btn-ghost">Batal</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section>
    <div class="judul-seksi"><h3>Daftar Promo <span class="jumlah"><?= count($daftar) ?></span></h3></div>
    <?php if (count($daftar) === 0): ?>
      <div class="card kosong-besar"><?= ikon('gift', 40) ?><p>Belum ada promo. Yuk buat promo pertamamu!</p></div>
    <?php endif; ?>
    <div class="grid-promo">
      <?php foreach ($daftar as $p): include 'includes/kartu_promo.php'; endforeach; ?>
    </div>
  </section>
</div>

<?php require 'includes/footer.php'; ?>
