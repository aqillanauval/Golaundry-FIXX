<?php
class LayananModel extends Model
{
    protected $tabel = 'layanan';
    public function semua()
    {
        $stmt = $this->db->query("SELECT * FROM layanan ORDER BY id");
        return $stmt->fetchAll();
    }

    public function tambah($nama, $harga, $satuan)
    {
        $stmt = $this->db->prepare("INSERT INTO layanan (nama, harga, satuan) VALUES (:nama, :harga, :satuan)");
        $stmt->execute(['nama' => $nama, 'harga' => $harga, 'satuan' => $satuan]);
    }

    public function ubah($id, $nama, $harga, $satuan)
    {
        $stmt = $this->db->prepare("UPDATE layanan SET nama = :nama, harga = :harga, satuan = :satuan WHERE id = :id");
        $stmt->execute(['nama' => $nama, 'harga' => $harga, 'satuan' => $satuan, 'id' => (int)$id]);
    }

}