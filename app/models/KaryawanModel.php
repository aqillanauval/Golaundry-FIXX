<?php
// KaryawanModel.php  -  CRUD tabel karyawan

class KaryawanModel extends Model
{
    protected string $tabel = 'karyawan';

    public function semua(): array
    {
        return $this->db->query('SELECT * FROM karyawan ORDER BY nama')->fetchAll();
    }

    public function tambah(string $nama, string $jabatan, string $noHp): int
    {
        $this->jalankan(
            'INSERT INTO karyawan (nama, jabatan, no_hp) VALUES (:nama, :jabatan, :no_hp)',
            ['nama' => $nama, 'jabatan' => $jabatan, 'no_hp' => $noHp]
        );
        return (int)$this->db->lastInsertId();
    }

    public function ubah(int $id, string $nama, string $jabatan, string $noHp): void
    {
        $this->jalankan(
            'UPDATE karyawan SET nama = :nama, jabatan = :jabatan, no_hp = :no_hp WHERE id = :id',
            ['nama' => $nama, 'jabatan' => $jabatan, 'no_hp' => $noHp, 'id' => $id]
        );
    }
}
