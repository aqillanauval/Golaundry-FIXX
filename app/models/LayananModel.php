<?php
// LayananModel.php  ->  CRUD tabel layanan (Cuci Kering, Cuci Setrika, dll)

class LayananModel extends Model
{
    protected $tabel = 'layanan';

    // READ: semua layanan. Pakai query() karena nggak ada input dari user
    public function semua()
    {
        $stmt = $this->db->query("SELECT * FROM layanan ORDER BY id");
        return $stmt->fetchAll();
    }

    // CREATE
    public function tambah($nama, $harga, $satuan)
    {
        $stmt = $this->db->prepare("INSERT INTO layanan (nama, harga, satuan) VALUES (:nama, :harga, :satuan)");
        $stmt->execute(['nama' => $nama, 'harga' => $harga, 'satuan' => $satuan]);
    }

    // UPDATE (wajib pakai WHERE biar yang berubah cuma 1 baris)
    public function ubah($id, $nama, $harga, $satuan)
    {
        $stmt = $this->db->prepare("UPDATE layanan SET nama = :nama, harga = :harga, satuan = :satuan WHERE id = :id");
        $stmt->execute(['nama' => $nama, 'harga' => $harga, 'satuan' => $satuan, 'id' => (int)$id]);
    }

    // DELETE udah ada di class Model (hapus)
}
