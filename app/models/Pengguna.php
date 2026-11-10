<?php
class Pengguna
{
    private $id;
    private $username;
    private $nama;
    private $password; 
    protected $role;   
    public function __construct($data)
    {
        $this->id = (int)$data['id'];
        $this->username = $data['username'];
        $this->nama = $data['nama'];
        $this->password = $data['password'];
    }

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

    public function setNama($nama)
    {
        $this->nama = $nama;
    }

    public function cekPassword($passwordDiketik)
    {
        return password_verify($passwordDiketik, $this->password);
    }

    public function isAdmin()
    {
        return $this->role == 'admin';
    }

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
