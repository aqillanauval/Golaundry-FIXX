<?php
// ============================================================
// Pengguna.php  -  Kelas induk untuk semua pengguna sistem.
// (Sama seperti contoh pengurusBEM -> menteri di Modul 12.)
//
//   Pengguna (abstract)
//     |-- Admin      : staff / admin laundry
//     '-- Pelanggan  : customer
//
// Enkapsulasi: properti dibuat private/protected, diakses lewat getter.
// Password TIDAK punya getter: hanya bisa dicek lewat cekPassword().
// Polimorfisme: namaRole(), urlDashboard(), menu() isinya berbeda
// di Admin dan Pelanggan, tapi dipanggil dengan cara yang sama.
// ============================================================

abstract class Pengguna
{
    private int $id;
    private string $username;
    private string $nama;
    private string $password; // berisi hash, bukan password asli
    protected string $role = '';

    public function __construct(array $baris)
    {
        $this->id = (int)$baris['id'];
        $this->username = (string)$baris['username'];
        $this->nama = (string)$baris['nama'];
        $this->password = (string)$baris['password'];
    }

    // "factory": buat objek Admin atau Pelanggan sesuai kolom role di database
    public static function dariBaris(array $baris): Pengguna
    {
        return ($baris['role'] === 'admin') ? new Admin($baris) : new Pelanggan($baris);
    }

    public function getId(): int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getNama(): string { return $this->nama; }
    public function getRole(): string { return $this->role; }

    public function setNama(string $nama): void
    {
        $this->nama = $nama;
    }

    public function cekPassword(string $diketik): bool
    {
        return password_verify($diketik, $this->password);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // ----- wajib diisi oleh kelas turunan -----
    abstract public function namaRole(): string;
    abstract public function urlDashboard(): string;
    abstract public function menu(): array; // [ [file, label, ikon], ... ]
}
