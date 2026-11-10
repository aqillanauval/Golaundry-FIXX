<?php
// KaryawanModel.php  ->  CRUD tabel karyawan

class KaryawanModel extends Model
{
    protected $tabel = 'karyawan';

    public function semua()
    {
        $stmt = $this->db->query("SELECT * FROM karyawan ORDER BY nama");
        return $stmt->fetchAll();
    }

    public function tambah($nama, $jabatan, $noHp)
    {
        $stmt = $this->db->prepare("INSERT INTO karyawan (nama, jabatan, no_hp) VALUES (:nama, :jabatan, :no_hp)");
        $stmt->execute(['nama' => $nama, 'jabatan' => $jabatan, 'no_hp' => $noHp]);
    }

    public function ubah($id, $nama, $jabatan, $noHp)
    {
        $stmt = $this->db->prepare("UPDATE karyawan SET nama = :nama, jabatan = :jabatan, no_hp = :no_hp WHERE id = :id");
        $stmt->execute(['nama' => $nama, 'jabatan' => $jabatan, 'no_hp' => $noHp, 'id' => (int)$id]);
    }
}
