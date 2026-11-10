<?php
// ============================================================
// Model.php  -  Kelas induk (parent) untuk semua model.
//
// OOP:
//   - abstract   : kelas ini tidak dibuat langsung, hanya diturunkan
//   - protected  : properti/method bisa dipakai kelas turunan, tapi tidak dari luar
//   - extends    : LayananModel, PromoModel, dst. mewarisi method di sini
//
// Semua query yang menerima input pengguna WAJIB lewat prepare() + execute(),
// supaya data dan perintah SQL dikirim terpisah (aman dari SQL injection).
// ============================================================

abstract class Model
{
    protected PDO $db;
    protected string $tabel = ''; // diisi kelas turunan, contoh: 'layanan'

    public function __construct()
    {
        $this->db = Database::koneksi();
    }

    // ---------- method bantu untuk kelas turunan ----------

    // jalankan prepared statement, kembalikan PDOStatement
    protected function jalankan(string $sql, array $param = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($param);
        return $stmt;
    }

    // ambil satu baris (array asosiatif) atau null kalau tidak ada
    protected function satuBaris(string $sql, array $param = []): ?array
    {
        $baris = $this->jalankan($sql, $param)->fetch(); // fetch() = false kalau kosong
        return $baris === false ? null : $baris;
    }

    // ambil semua baris
    protected function semuaBaris(string $sql, array $param = []): array
    {
        return $this->jalankan($sql, $param)->fetchAll();
    }

    // ---------- CRUD umum yang sama untuk semua tabel ----------

    public function cari(int $id): ?array
    {
        return $this->satuBaris("SELECT * FROM {$this->tabel} WHERE id = :id", ['id' => $id]);
    }

    // DELETE selalu memakai WHERE. Mengembalikan true kalau ada baris yang terhapus.
    public function hapus(int $id): bool
    {
        return $this->jalankan("DELETE FROM {$this->tabel} WHERE id = :id", ['id' => $id])->rowCount() > 0;
    }

    public function jumlah(): int
    {
        // query() boleh dipakai karena tidak ada input pengguna
        return (int)$this->db->query("SELECT COUNT(*) FROM {$this->tabel}")->fetchColumn();
    }
}
