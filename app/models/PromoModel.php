<?php
// ============================================================
// PromoModel.php  -  CRUD tabel promo + file foto di uploads/promo/
// Data promo ada di database, file fotonya tetap di folder (operasi file).
// ============================================================

class PromoModel extends Model
{
    protected string $tabel = 'promo';

    public function semua(): array
    {
        return $this->db->query('SELECT * FROM promo ORDER BY id DESC')->fetchAll();
    }

    // promo yang masih berlaku hari ini
    public function aktif(): array
    {
        return $this->semuaBaris(
            'SELECT * FROM promo WHERE berlaku_sampai >= :hari ORDER BY berlaku_sampai',
            ['hari' => date('Y-m-d')]
        );
    }

    public function cariAktif(int $id): ?array
    {
        return $this->satuBaris(
            'SELECT * FROM promo WHERE id = :id AND berlaku_sampai >= :hari',
            ['id' => $id, 'hari' => date('Y-m-d')]
        );
    }

    public static function masihBerlaku(array $promo): bool
    {
        return $promo['berlaku_sampai'] >= date('Y-m-d'); // format Y-m-d bisa dibandingkan langsung
    }

    public function tambah(string $judul, string $deskripsi, int $diskon, string $sampai, string $foto): int
    {
        $this->jalankan(
            'INSERT INTO promo (judul, deskripsi, diskon, berlaku_sampai, foto)
             VALUES (:judul, :deskripsi, :diskon, :sampai, :foto)',
            ['judul' => $judul, 'deskripsi' => $deskripsi, 'diskon' => $diskon, 'sampai' => $sampai, 'foto' => $foto]
        );
        return (int)$this->db->lastInsertId();
    }

    // $fotoBaru = '' artinya foto tidak diganti
    public function ubah(int $id, string $judul, string $deskripsi, int $diskon, string $sampai, string $fotoBaru): void
    {
        $lama = $this->cari($id);
        if (!$lama) {
            return;
        }
        $this->jalankan(
            'UPDATE promo SET judul = :judul, deskripsi = :deskripsi, diskon = :diskon,
                    berlaku_sampai = :sampai, foto = :foto
             WHERE id = :id',
            [
                'judul' => $judul, 'deskripsi' => $deskripsi, 'diskon' => $diskon, 'sampai' => $sampai,
                'foto' => $fotoBaru !== '' ? $fotoBaru : $lama['foto'], 'id' => $id,
            ]
        );
        if ($fotoBaru !== '') {
            self::hapusFoto($lama['foto']); // foto lama dibuang setelah database berhasil diubah
        }
    }

    // hapus baris promo, lalu file fotonya
    public function hapus(int $id): bool
    {
        $promo = $this->cari($id);
        if (!$promo || !parent::hapus($id)) {
            return false;
        }
        self::hapusFoto($promo['foto']);
        return true;
    }

    // ---------- operasi file foto ----------

    public static function adaFoto(array $promo): bool
    {
        return $promo['foto'] !== '' && file_exists(FOLDER_UPLOAD . $promo['foto']);
    }

    public static function hapusFoto(string $namaFile): void
    {
        $namaFile = basename($namaFile); // cegah path seperti ../../file
        if ($namaFile !== '' && file_exists(FOLDER_UPLOAD . $namaFile)) {
            unlink(FOLDER_UPLOAD . $namaFile);
        }
    }

    // proses upload. Mengembalikan ['nama' => file] atau ['error' => pesan]
    public static function uploadFoto(array $berkas): array
    {
        $ekstensiBoleh = ['jpg', 'jpeg', 'png'];
        $ukuranMaks = 2 * 1024 * 1024; // 2 MB
        $ekstensi = strtolower(pathinfo((string)$berkas['name'], PATHINFO_EXTENSION));

        if ($berkas['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload foto gagal. Coba pilih file lain.'];
        }
        if (!in_array($ekstensi, $ekstensiBoleh, true)) {
            return ['error' => 'Foto harus berformat JPG atau PNG.'];
        }
        if ($berkas['size'] > $ukuranMaks) {
            return ['error' => 'Ukuran foto melebihi 2 MB.'];
        }
        if (@getimagesize($berkas['tmp_name']) === false) {
            return ['error' => 'File yang diunggah bukan gambar yang valid.'];
        }
        if (!file_exists(FOLDER_UPLOAD)) {
            mkdir(FOLDER_UPLOAD, 0775, true);
        }
        $namaBaru = uniqid('promo_') . '.' . $ekstensi; // nama acak, tidak bentrok
        if (!move_uploaded_file($berkas['tmp_name'], FOLDER_UPLOAD . $namaBaru)) {
            return ['error' => 'Foto gagal disimpan di server.'];
        }
        return ['nama' => $namaBaru];
    }
}
