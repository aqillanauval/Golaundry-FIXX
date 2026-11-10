<?php
// ============================================================
// PesananModel.php  ->  semua urusan tabel pesanan (transaksi laundry)
//
// Hasil SELECT diambil pakai PDO::FETCH_CLASS, jadi tiap baris langsung
// jadi objek Pesanan (lihat Pesanan.php).
//
// Nama pelanggan diambil pakai LEFT JOIN ke tabel users. Jadi kalau pelanggan
// ganti nama di profil, semua pesanannya ikut nampilin nama yang baru.
// ============================================================

class PesananModel extends Model
{
    protected $tabel = 'pesanan';

    // bagian SELECT yang sama untuk semua query di bawah
    private $sqlDasar = "
        SELECT p.*,
               COALESCE(u.nama, p.nama_pelanggan) AS pelanggan,
               ul.rating   AS ulasan_rating,
               ul.komentar AS ulasan_komentar
        FROM pesanan p
        LEFT JOIN users  u  ON u.id = p.user_id
        LEFT JOIN ulasan ul ON ul.pesanan_id = p.id";

    // ================= READ =================

    // semua pesanan (buat admin). Bisa sambil dicari pakai $kataKunci
    public function semua($kataKunci = '')
    {
        if ($kataKunci == '') {
            $stmt = $this->db->query($this->sqlDasar . " ORDER BY p.tanggal DESC, p.id DESC");
            return $stmt->fetchAll(PDO::FETCH_CLASS, 'Pesanan');
        }

        // pencarian AMAN pakai LIKE + parameter (materi PDO Langkah 7)
        // input user nggak pernah disambung langsung ke SQL, jadi ' OR '1'='1 nggak ngaruh
        $cari = '%' . strtolower($kataKunci) . '%';
        $stmt = $this->db->prepare($this->sqlDasar . "
            WHERE LOWER(p.kode) LIKE :cari1
               OR LOWER(COALESCE(u.nama, p.nama_pelanggan)) LIKE :cari2
               OR LOWER(p.nama_layanan) LIKE :cari3
            ORDER BY p.tanggal DESC, p.id DESC");
        $stmt->execute(['cari1' => $cari, 'cari2' => $cari, 'cari3' => $cari]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Pesanan');
    }

    // pesanan punya 1 pelanggan, terbaru di atas
    public function milikUser($userId)
    {
        $stmt = $this->db->prepare($this->sqlDasar . " WHERE p.user_id = :user_id ORDER BY p.tanggal DESC, p.id DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Pesanan');
    }

    // pesanan dari aplikasi yang masih nunggu dijemput kurir
    public function menungguJemput()
    {
        $stmt = $this->db->query($this->sqlDasar . " WHERE p.status = 'Menunggu Jemput' ORDER BY p.tanggal, p.id");
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Pesanan');
    }

    // 8 pesanan terbaru yang udah ditimbang
    public function sudahDitimbang()
    {
        $stmt = $this->db->query($this->sqlDasar . " WHERE p.berat > 0 ORDER BY p.tanggal DESC, p.id DESC LIMIT 8");
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Pesanan');
    }

    // pesanan yang masuk hari ini
    public function hariIni()
    {
        $stmt = $this->db->prepare($this->sqlDasar . " WHERE p.tanggal >= :awal AND p.tanggal < :akhir");
        $stmt->execute([
            'awal'  => date('Y-m-d') . ' 00:00:00',
            'akhir' => date('Y-m-d', strtotime('+1 day')) . ' 00:00:00',
        ]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Pesanan');
    }

    public function jumlahDiproses()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM pesanan WHERE status IN ('Diterima', 'Dicuci')");
        return (int)$stmt->fetchColumn();
    }

    // cari 1 pesanan pakai No. Nota
    public function cariByKode($kode)
    {
        $stmt = $this->db->prepare($this->sqlDasar . " WHERE p.kode = :kode");
        $stmt->execute(['kode' => $kode]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Pesanan');
        $pesanan = $stmt->fetch();

        if (!$pesanan) {
            return null;
        }
        return $pesanan;
    }

    // sama kayak di atas, tapi harus punya user ini (biar nggak bisa intip pesanan orang lain)
    public function cariMilikUser($kode, $userId)
    {
        $stmt = $this->db->prepare($this->sqlDasar . " WHERE p.kode = :kode AND p.user_id = :user_id");
        $stmt->execute(['kode' => $kode, 'user_id' => $userId]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Pesanan');
        $pesanan = $stmt->fetch();

        if (!$pesanan) {
            return null;
        }
        return $pesanan;
    }

    // ================= CREATE =================

    // $data berisi: user_id, nama_pelanggan, sumber, layanan (array), berat, catatan, status, promo (array / null)
    // Harga, nama layanan, nama promo & diskon DISALIN ke pesanan,
    // biar nota lama nggak ikut berubah kalau admin ganti harga atau hapus promo.
    public function buat($data)
    {
        $kode = uniqid('ORD'); // No. Nota acak, contoh: ORD6ab7da28cce8f
        $layanan = $data['layanan'];
        $promo = $data['promo'];

        $stmt = $this->db->prepare(
            "INSERT INTO pesanan
                (kode, user_id, nama_pelanggan, sumber, layanan_id, nama_layanan, harga, satuan,
                 berat, catatan, status, promo_id, nama_promo, diskon, tanggal)
             VALUES
                (:kode, :user_id, :nama_pelanggan, :sumber, :layanan_id, :nama_layanan, :harga, :satuan,
                 :berat, :catatan, :status, :promo_id, :nama_promo, :diskon, :tanggal)"
        );
        $stmt->execute([
            'kode'           => $kode,
            'user_id'        => $data['user_id'],
            'nama_pelanggan' => $data['nama_pelanggan'],
            'sumber'         => $data['sumber'],
            'layanan_id'     => $layanan['id'],
            'nama_layanan'   => $layanan['nama'],
            'harga'          => $layanan['harga'],
            'satuan'         => $layanan['satuan'],
            'berat'          => $data['berat'],
            'catatan'        => $data['catatan'],
            'status'         => $data['status'],
            'promo_id'       => $promo ? $promo['id'] : null,
            'nama_promo'     => $promo ? $promo['judul'] : '',
            'diskon'         => $promo ? $promo['diskon'] : 0,
            'tanggal'        => date('Y-m-d H:i:s'),
        ]);
        return $kode;
    }

    // ================= UPDATE =================

    // pelanggan ngedit pesanannya sendiri (cuma boleh selama "Menunggu Jemput")
    // $promo: array promo baru, null = tanpa promo, 'lama' = promonya nggak diganti
    public function ubahOlehPelanggan($kode, $userId, $layanan, $catatan, $promo)
    {
        $lama = $this->cariMilikUser($kode, $userId);
        if (!$lama || !$lama->menungguJemput()) {
            return false;
        }

        if ($promo == 'lama') {
            $promoId = $lama->promo_id;
            $namaPromo = $lama->nama_promo;
            $diskon = $lama->diskon;
        } elseif ($promo) {
            $promoId = $promo['id'];
            $namaPromo = $promo['judul'];
            $diskon = $promo['diskon'];
        } else {
            $promoId = null;
            $namaPromo = '';
            $diskon = 0;
        }

        $stmt = $this->db->prepare(
            "UPDATE pesanan
             SET layanan_id = :layanan_id, nama_layanan = :nama_layanan, harga = :harga, satuan = :satuan,
                 catatan = :catatan, promo_id = :promo_id, nama_promo = :nama_promo, diskon = :diskon
             WHERE kode = :kode AND user_id = :user_id AND status = 'Menunggu Jemput'"
        );
        $stmt->execute([
            'layanan_id' => $layanan['id'], 'nama_layanan' => $layanan['nama'],
            'harga' => $layanan['harga'], 'satuan' => $layanan['satuan'], 'catatan' => $catatan,
            'promo_id' => $promoId, 'nama_promo' => $namaPromo, 'diskon' => $diskon,
            'kode' => $kode, 'user_id' => $userId,
        ]);
        return true;
    }

    // admin ngedit transaksi (promonya nggak diubah)
    public function ubahOlehAdmin($kode, $layanan, $berat, $status, $catatan)
    {
        $stmt = $this->db->prepare(
            "UPDATE pesanan
             SET layanan_id = :layanan_id, nama_layanan = :nama_layanan, harga = :harga, satuan = :satuan,
                 berat = :berat, status = :status, catatan = :catatan
             WHERE kode = :kode"
        );
        $stmt->execute([
            'layanan_id' => $layanan['id'], 'nama_layanan' => $layanan['nama'],
            'harga' => $layanan['harga'], 'satuan' => $layanan['satuan'],
            'berat' => $berat, 'status' => $status, 'catatan' => $catatan, 'kode' => $kode,
        ]);
    }

    // kurir udah nimbang -> isi berat, status jadi "Diterima"
    public function isiBerat($kode, $berat)
    {
        $stmt = $this->db->prepare(
            "UPDATE pesanan SET berat = :berat, status = 'Diterima'
             WHERE kode = :kode AND status = 'Menunggu Jemput'"
        );
        $stmt->execute(['berat' => $berat, 'kode' => $kode]);
        return $stmt->rowCount() == 1; // true kalau berhasil
    }

    // dipanggil lewat AJAX di dashboard admin
    public function ubahStatus($kode, $status)
    {
        $stmt = $this->db->prepare("UPDATE pesanan SET status = :status WHERE kode = :kode AND berat > 0");
        $stmt->execute(['status' => $status, 'kode' => $kode]);
    }

    // ================= DELETE =================

    // pelanggan batalin pesanan (cuma boleh selama "Menunggu Jemput")
    public function batalkan($kode, $userId)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM pesanan WHERE kode = :kode AND user_id = :user_id AND status = 'Menunggu Jemput'"
        );
        $stmt->execute(['kode' => $kode, 'user_id' => $userId]);
        return $stmt->rowCount() == 1;
    }

    // admin hapus transaksi (ulasannya ikut kehapus karena ON DELETE CASCADE)
    public function hapusByKode($kode)
    {
        $stmt = $this->db->prepare("DELETE FROM pesanan WHERE kode = :kode");
        $stmt->execute(['kode' => $kode]);
        return $stmt->rowCount() == 1;
    }
}
