<?php
// ============================================================
// Pengguna.php  ->  class INDUK buat semua pengguna
// (mirip contoh class pengurusBEM -> menteri di Modul 12)
//
//   Pengguna
//     |-- Admin      (staff laundry)
//     '-- Pelanggan  (customer)
//
// - Properti dibikin private, jadi dari luar class harus lewat getter (getNama(), dll).
//   Ini namanya ENKAPSULASI.
// - Password sengaja nggak dikasih getter. Cuma bisa dicek pakai cekPassword().
// - Admin & Pelanggan punya getMenu() masing-masing dengan isi beda.
//   Cara manggilnya sama, hasilnya beda. Ini namanya POLIMORFISME.
// ============================================================

class Pengguna
{
    private $id;
    private $username;
    private $nama;
    private $password; // isinya hash dari password_hash(), bukan password asli
    protected $role;   // protected = bisa diisi sama class anak (Admin / Pelanggan)

    // $data = 1 baris dari tabel users
    public function __construct($data)
    {
        $this->id = (int)$data['id'];
        $this->username = $data['username'];
        $this->nama = $data['nama'];
        $this->password = $data['password'];
    }

    // ----- getter -----
    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getNama()
    {
        return $this->nama;
    }

    public function getRole()
    {
        return $this->role;
    }

    // ----- setter -----
    public function setNama($nama)
    {
        $this->nama = $nama;
    }

    // cocokin password yang diketik sama hash di database
    public function cekPassword($passwordDiketik)
    {
        return password_verify($passwordDiketik, $this->password);
    }

    public function isAdmin()
    {
        return $this->role == 'admin';
    }

    // ----- nanti ditimpa (override) sama class anak -----
    public function getNamaRole()
    {
        return 'Pengguna';
    }

    public function getHalamanDashboard()
    {
        return 'login.php';
    }

    public function getMenu()
    {
        return [];
    }
}
