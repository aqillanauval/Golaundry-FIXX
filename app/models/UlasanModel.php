<?php
// UlasanModel.php  ->  CRUD tabel ulasan (1 pesanan maksimal 1 ulasan)

class UlasanModel extends Model
{
    protected $tabel = 'ulasan';

    public function cariByPesanan($pesananId)
    {
        $stmt = $this->db->prepare("SELECT * FROM ulasan WHERE pesanan_id = :pesanan_id");
        $stmt->execute(['pesanan_id' => $pesananId]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }
        return $data;
    }

    // kalau belum ada ulasan -> INSERT, kalau udah ada -> UPDATE
    public function simpan($pesananId, $rating, $komentar)
    {
        if ($this->cariByPesanan($pesananId)) {
            $stmt = $this->db->prepare("UPDATE ulasan SET rating = :rating, komentar = :komentar WHERE pesanan_id = :pesanan_id");
        } else {
            $stmt = $this->db->prepare("INSERT INTO ulasan (pesanan_id, rating, komentar) VALUES (:pesanan_id, :rating, :komentar)");
        }
        $stmt->execute(['pesanan_id' => $pesananId, 'rating' => $rating, 'komentar' => $komentar]);
    }

    public function hapusByPesanan($pesananId)
    {
        $stmt = $this->db->prepare("DELETE FROM ulasan WHERE pesanan_id = :pesanan_id");
        $stmt->execute(['pesanan_id' => $pesananId]);
    }
}
