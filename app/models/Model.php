<?php
// ============================================================
// Model.php  ->  class INDUK buat semua model
//
// Semua model (LayananModel, PromoModel, dll) "extends Model",
// jadi otomatis punya $this->db dan fungsi cari() & hapus() di bawah ini.
// Nggak perlu nulis ulang di tiap model (konsep pewarisan / inheritance).
// ============================================================

class Model
{
    protected $db;     // koneksi PDO
    protected $tabel;  // nama tabel, diisi sama class anaknya

    public function __construct()
    {
        global $pdo;   // $pdo dibikin di koneksi.php
        $this->db = $pdo;
    }

    // ambil 1 baris berdasarkan id. Kalau nggak ketemu hasilnya null
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

    // hapus 1 baris. INGAT: DELETE harus selalu pakai WHERE!
    public function hapus($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->tabel} WHERE id = :id");
        $stmt->execute(['id' => (int)$id]); // (int) = paksa jadi angka
        return $stmt->rowCount() > 0; // true kalau ada yang kehapus
    }
}
