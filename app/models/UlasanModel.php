<?php
// UlasanModel.php  -  CRUD tabel ulasan (satu pesanan maksimal satu ulasan)

class UlasanModel extends Model
{
    protected string $tabel = 'ulasan';

    public function untukPesanan(int $pesananId): ?array
    {
        return $this->satuBaris('SELECT * FROM ulasan WHERE pesanan_id = :p', ['p' => $pesananId]);
    }

    // CREATE kalau belum ada, UPDATE kalau sudah ada
    public function simpan(int $pesananId, int $rating, string $komentar): void
    {
        if ($this->untukPesanan($pesananId)) {
            $this->jalankan(
                'UPDATE ulasan SET rating = :rating, komentar = :komentar WHERE pesanan_id = :p',
                ['rating' => $rating, 'komentar' => $komentar, 'p' => $pesananId]
            );
        } else {
            $this->jalankan(
                'INSERT INTO ulasan (pesanan_id, rating, komentar) VALUES (:p, :rating, :komentar)',
                ['p' => $pesananId, 'rating' => $rating, 'komentar' => $komentar]
            );
        }
    }

    public function hapusUntukPesanan(int $pesananId): void
    {
        $this->jalankan('DELETE FROM ulasan WHERE pesanan_id = :p', ['p' => $pesananId]);
    }
}
