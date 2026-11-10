<?php
// LayananModel.php  -  CRUD tabel layanan

class LayananModel extends Model
{
    protected string $tabel = 'layanan';

    public function semua(): array
    {
        return $this->db->query('SELECT * FROM layanan ORDER BY id')->fetchAll();
    }

    public function tambah(string $nama, int $harga, string $satuan): int
    {
        $this->jalankan(
            'INSERT INTO layanan (nama, harga, satuan) VALUES (:nama, :harga, :satuan)',
            ['nama' => $nama, 'harga' => $harga, 'satuan' => $satuan]
        );
        return (int)$this->db->lastInsertId();
    }

    public function ubah(int $id, string $nama, int $harga, string $satuan): void
    {
        $this->jalankan(
            'UPDATE layanan SET nama = :nama, harga = :harga, satuan = :satuan WHERE id = :id',
            ['nama' => $nama, 'harga' => $harga, 'satuan' => $satuan, 'id' => $id]
        );
    }
}
