<?php
class Model
{
    protected $db;     
    protected $tabel;  

    public function __construct()
    {
        global $pdo;   
        $this->db = $pdo;
    }

    public function cari($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->tabel} WHERE id = :id");
        $stmt->execute(['id' => (int)$id]); // (int) = paksa jadi angka
        $data = $stmt->fetch(); // fetch() ngasih false kalau datanya nggak ada

        if (!$data) {
            return null;
        }
        return $data;
    }

    public function hapus($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->tabel} WHERE id = :id");
        $stmt->execute(['id' => (int)$id]); 
        return $stmt->rowCount() > 0; 
    }
}
