<?php
// ============================================================
// PromoModel.php  ->  CRUD tabel promo + urusan file foto promo
// Data promo disimpan di database, tapi file fotonya tetap di folder
// uploads/promo/ (yang disimpan di database cuma nama filenya).
// ============================================================

class PromoModel extends Model
{
    protected $tabel = 'promo';

    public function semua()
    {
        $stmt = $this->db->query("SELECT * FROM promo ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    // promo yang masih berlaku hari ini
    public function aktif()
    {
        $stmt = $this->db->prepare("SELECT * FROM promo WHERE berlaku_sampai >= :hari_ini ORDER BY berlaku_sampai");
        $stmt->execute(['hari_ini' => date('Y-m-d')]);
        return $stmt->fetchAll();
    }

    // cari promo tapi cuma kalau masih berlaku (biar user nggak bisa pakai promo kadaluarsa)
    public function cariAktif($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM promo WHERE id = :id AND berlaku_sampai >= :hari_ini");
        $stmt->execute(['id' => (int)$id, 'hari_ini' => date('Y-m-d')]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }
        return $data;
    }

    public function tambah($judul, $deskripsi, $diskon, $sampai, $foto)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO promo (judul, deskripsi, diskon, berlaku_sampai, foto)
             VALUES (:judul, :deskripsi, :diskon, :sampai, :foto)"
        );
        $stmt->execute([
            'judul' => $judul, 'deskripsi' => $deskripsi, 'diskon' => $diskon,
            'sampai' => $sampai, 'foto' => $foto,
        ]);
    }

    // $fotoBaru = '' artinya fotonya nggak diganti
    public function ubah($id, $judul, $deskripsi, $diskon, $sampai, $fotoBaru)
    {
        $lama = $this->cari($id);
        if (!$lama) {
            return;
        }

        $foto = $lama['foto'];
        if ($fotoBaru != '') {
            $foto = $fotoBaru;
        }

        $stmt = $this->db->prepare(
            "UPDATE promo SET judul = :judul, deskripsi = :deskripsi, diskon = :diskon,
                              berlaku_sampai = :sampai, foto = :foto
             WHERE id = :id"
        );
        $stmt->execute([
            'judul' => $judul, 'deskripsi' => $deskripsi, 'diskon' => $diskon,
            'sampai' => $sampai, 'foto' => $foto, 'id' => (int)$id,
        ]);

        // foto lama dibuang kalau udah diganti foto baru
        if ($fotoBaru != '') {
            $this->hapusFoto($lama['foto']);
        }
    }

    // hapus promo + file fotonya (method hapus() punya Model "ditimpa"/override di sini)
    public function hapus($id)
    {
        $promo = $this->cari($id);
        if (!$promo) {
            return false;
        }
        parent::hapus($id);              // jalanin hapus() punya class induk (Model)
        $this->hapusFoto($promo['foto']); // terus hapus file fotonya
        return true;
    }

    // ---------- urusan file foto ----------

    public function hapusFoto($namaFile)
    {
        $namaFile = basename($namaFile); // jaga-jaga biar nggak bisa ngehapus file di folder lain
        if ($namaFile != '' && file_exists(FOLDER_UPLOAD . $namaFile)) {
            unlink(FOLDER_UPLOAD . $namaFile);
        }
    }

    // proses upload foto. Hasilnya ['nama' => namafile] atau ['error' => pesan]
    public function uploadFoto($berkas)
    {
        $ekstensiBoleh = ['jpg', 'jpeg', 'png'];
        $ukuranMaks = 2 * 1024 * 1024; // 2 MB
        $ekstensi = strtolower(pathinfo($berkas['name'], PATHINFO_EXTENSION));

        if ($berkas['error'] != UPLOAD_ERR_OK) {
            return ['error' => 'Upload foto gagal. Coba pilih file lain.'];
        }
        if (!in_array($ekstensi, $ekstensiBoleh)) {
            return ['error' => 'Foto harus JPG atau PNG.'];
        }
        if ($berkas['size'] > $ukuranMaks) {
            return ['error' => 'Ukuran foto kegedean, maksimal 2 MB.'];
        }
        if (@getimagesize($berkas['tmp_name']) === false) {
            return ['error' => 'File yang diupload bukan gambar.'];
        }

        if (!file_exists(FOLDER_UPLOAD)) {
            mkdir(FOLDER_UPLOAD, 0775, true);
        }
        $namaBaru = uniqid('promo_') . '.' . $ekstensi; // nama acak biar nggak bentrok
        if (!move_uploaded_file($berkas['tmp_name'], FOLDER_UPLOAD . $namaBaru)) {
            return ['error' => 'Foto gagal disimpan di server.'];
        }
        return ['nama' => $namaBaru];
    }
}
