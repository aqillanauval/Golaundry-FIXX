<?php
// ============================================================
// PesananModel.php  -  Akses tabel pesanan (transaksi laundry)
//
// Hasil SELECT diambil dengan PDO::FETCH_CLASS, jadi setiap baris langsung
// menjadi objek Pesanan (lihat app/entities/Pesanan.php).
//
// Nama pelanggan diambil dengan LEFT JOIN ke users, supaya kalau pelanggan
// mengganti namanya di profil, semua pesanannya ikut tampil dengan nama baru.
// ============================================================

class PesananModel extends Model
{
    protected string $tabel = 'pesanan';

    private const SELECT_DASAR = "
        SELECT p.id, p.kode, p.user_id, p.nama_pelanggan, p.sumber, p.layanan_id, p.nama_layanan,
               p.harga, p.satuan, p.berat, p.catatan, p.status, p.promo_id, p.nama_promo, p.diskon,
               p.tanggal,
               COALESCE(u.nama, p.nama_pelanggan) AS pelanggan,
               ul.rating   AS ulasan_rating,
               ul.komentar AS ulasan_komentar
        FROM pesanan p
        LEFT JOIN users  u  ON u.id = p.user_id
        LEFT JOIN ulasan ul ON ul.pesanan_id = p.id";

    private const URUT_TERBARU = ' ORDER BY p.tanggal DESC, p.id DESC';

    // ---------- ambil data sebagai objek Pesanan ----------

    /** @return Pesanan[] */
    private function daftarObjek(string $sql, array $param = []): array
    {
        return $this->jalankan($sql, $param)->fetchAll(PDO::FETCH_CLASS, Pesanan::class);
    }

    private function satuObjek(string $sql, array $param = []): ?Pesanan
    {
        $stmt = $this->jalankan($sql, $param);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Pesanan::class);
        $hasil = $stmt->fetch();
        return $hasil === false ? null : $hasil;
    }

    // ---------- READ ----------

    // semua pesanan (admin). $cari: pencarian aman dengan LIKE + parameter
    public function semua(string $cari = ''): array
    {
        if ($cari === '') {
            return $this->daftarObjek(self::SELECT_DASAR . self::URUT_TERBARU);
        }
        // karakter % dan _ dari pengguna dianggap huruf biasa, bukan wildcard
        $pola = '%' . addcslashes(strtolower($cari), '%_\\') . '%';
        return $this->daftarObjek(
            self::SELECT_DASAR . '
             WHERE LOWER(p.kode) LIKE :q1
                OR LOWER(COALESCE(u.nama, p.nama_pelanggan)) LIKE :q2
                OR LOWER(p.nama_layanan) LIKE :q3' . self::URUT_TERBARU,
            ['q1' => $pola, 'q2' => $pola, 'q3' => $pola]
        );
    }

    // pesanan milik satu pelanggan. $batas = 0 berarti semua
    public function milikUser(int $userId, int $batas = 0): array
    {
        $sql = self::SELECT_DASAR . ' WHERE p.user_id = :uid' . self::URUT_TERBARU;
        if ($batas > 0) {
            $sql .= ' LIMIT ' . (int)$batas; // angka sudah dipaksa jadi int, aman
        }
        return $this->daftarObjek($sql, ['uid' => $userId]);
    }

    public function menungguJemput(): array
    {
        return $this->daftarObjek(
            self::SELECT_DASAR . " WHERE p.status = 'Menunggu Jemput' ORDER BY p.tanggal, p.id"
        );
    }

    // pesanan yang sudah ditimbang, terbaru di atas
    public function sudahDitimbang(int $batas = 8): array
    {
        return $this->daftarObjek(
            self::SELECT_DASAR . ' WHERE p.berat > 0' . self::URUT_TERBARU . ' LIMIT ' . (int)$batas
        );
    }

    public function hariIni(): array
    {
        return $this->daftarObjek(
            self::SELECT_DASAR . ' WHERE p.tanggal >= :awal AND p.tanggal < :akhir',
            ['awal' => date('Y-m-d 00:00:00'), 'akhir' => date('Y-m-d 00:00:00', strtotime('+1 day'))]
        );
    }

    public function jumlahDiproses(): int
    {
        return (int)$this->db
            ->query("SELECT COUNT(*) FROM pesanan WHERE status IN ('Diterima', 'Dicuci')")
            ->fetchColumn();
    }

    public function cariByKode(string $kode): ?Pesanan
    {
        return $this->satuObjek(self::SELECT_DASAR . ' WHERE p.kode = :kode', ['kode' => $kode]);
    }

    public function cariMilikUser(string $kode, int $userId): ?Pesanan
    {
        return $this->satuObjek(
            self::SELECT_DASAR . ' WHERE p.kode = :kode AND p.user_id = :uid',
            ['kode' => $kode, 'uid' => $userId]
        );
    }

    // ---------- CREATE ----------

    // $d: user_id, nama_pelanggan, sumber, layanan(array), berat, catatan, status, promo(array|null)
    public function buat(array $d): string
    {
        $kode = uniqid('ORD');
        $layanan = $d['layanan'];
        $promo = $d['promo'];

        $this->jalankan(
            'INSERT INTO pesanan
               (kode, user_id, nama_pelanggan, sumber, layanan_id, nama_layanan, harga, satuan,
                berat, catatan, status, promo_id, nama_promo, diskon, tanggal)
             VALUES
               (:kode, :user_id, :nama_pelanggan, :sumber, :layanan_id, :nama_layanan, :harga, :satuan,
                :berat, :catatan, :status, :promo_id, :nama_promo, :diskon, :tanggal)',
            [
                'kode'           => $kode,
                'user_id'        => $d['user_id'],
                'nama_pelanggan' => $d['nama_pelanggan'],
                'sumber'         => $d['sumber'],
                'layanan_id'     => (int)$layanan['id'],
                'nama_layanan'   => $layanan['nama'],
                'harga'          => (int)$layanan['harga'],
                'satuan'         => $layanan['satuan'],
                'berat'          => $d['berat'],
                'catatan'        => $d['catatan'],
                'status'         => $d['status'],
                'promo_id'       => $promo ? (int)$promo['id'] : null,
                'nama_promo'     => $promo ? $promo['judul'] : '',
                'diskon'         => $promo ? (int)$promo['diskon'] : 0,
                'tanggal'        => date('Y-m-d H:i:s'),
            ]
        );
        return $kode;
    }

    // ---------- UPDATE ----------

    // pelanggan mengubah pesanannya sendiri, hanya selama "Menunggu Jemput".
    // $promo: array promo baru, null = tanpa promo, 'lama' = promo tidak diubah
    public function ubahOlehPelanggan(string $kode, int $userId, array $layanan, string $catatan, $promo): bool
    {
        $lama = $this->cariMilikUser($kode, $userId);
        if (!$lama || !$lama->menungguJemput()) {
            return false;
        }
        if ($promo === 'lama') {
            $promoId = $lama->promo_id;
            $namaPromo = $lama->nama_promo;
            $diskon = $lama->diskon();
        } else {
            $promoId = $promo ? (int)$promo['id'] : null;
            $namaPromo = $promo ? $promo['judul'] : '';
            $diskon = $promo ? (int)$promo['diskon'] : 0;
        }

        $this->jalankan(
            "UPDATE pesanan
                SET layanan_id = :layanan_id, nama_layanan = :nama_layanan, harga = :harga, satuan = :satuan,
                    catatan = :catatan, promo_id = :promo_id, nama_promo = :nama_promo, diskon = :diskon
              WHERE kode = :kode AND user_id = :uid AND status = 'Menunggu Jemput'",
            [
                'layanan_id' => (int)$layanan['id'], 'nama_layanan' => $layanan['nama'],
                'harga' => (int)$layanan['harga'], 'satuan' => $layanan['satuan'], 'catatan' => $catatan,
                'promo_id' => $promoId, 'nama_promo' => $namaPromo, 'diskon' => $diskon,
                'kode' => $kode, 'uid' => $userId,
            ]
        );
        return true;
    }

    // admin mengedit transaksi (promo tidak diubah)
    public function ubahOlehAdmin(string $kode, array $layanan, float $berat, string $status, string $catatan): bool
    {
        if (!$this->cariByKode($kode)) {
            return false;
        }
        $this->jalankan(
            'UPDATE pesanan
                SET layanan_id = :layanan_id, nama_layanan = :nama_layanan, harga = :harga, satuan = :satuan,
                    berat = :berat, status = :status, catatan = :catatan
              WHERE kode = :kode',
            [
                'layanan_id' => (int)$layanan['id'], 'nama_layanan' => $layanan['nama'],
                'harga' => (int)$layanan['harga'], 'satuan' => $layanan['satuan'],
                'berat' => $berat, 'status' => $status, 'catatan' => $catatan, 'kode' => $kode,
            ]
        );
        return true;
    }

    // kurir sudah menimbang: isi berat & status jadi "Diterima"
    public function isiBerat(string $kode, float $berat): bool
    {
        $stmt = $this->jalankan(
            "UPDATE pesanan SET berat = :berat, status = 'Diterima'
              WHERE kode = :kode AND status = 'Menunggu Jemput'",
            ['berat' => $berat, 'kode' => $kode]
        );
        return $stmt->rowCount() === 1;
    }

    // dipakai AJAX di dashboard admin. Hanya pesanan yang sudah ditimbang.
    public function ubahStatus(string $kode, string $status): bool
    {
        $p = $this->cariByKode($kode);
        if (!$p || !$p->sudahDitimbang()) {
            return false;
        }
        $this->jalankan(
            'UPDATE pesanan SET status = :status WHERE kode = :kode AND berat > 0',
            ['status' => $status, 'kode' => $kode]
        );
        return true;
    }

    // ---------- DELETE ----------

    // pelanggan membatalkan pesanannya (hanya selama "Menunggu Jemput")
    public function batalkan(string $kode, int $userId): bool
    {
        $stmt = $this->jalankan(
            "DELETE FROM pesanan WHERE kode = :kode AND user_id = :uid AND status = 'Menunggu Jemput'",
            ['kode' => $kode, 'uid' => $userId]
        );
        return $stmt->rowCount() === 1;
    }

    // admin menghapus transaksi (ulasannya ikut terhapus: ON DELETE CASCADE)
    public function hapusByKode(string $kode): bool
    {
        return $this->jalankan('DELETE FROM pesanan WHERE kode = :kode', ['kode' => $kode])->rowCount() === 1;
    }
}
