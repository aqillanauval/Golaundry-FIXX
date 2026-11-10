<?php
// Pelanggan.php  ->  anak dari class Pengguna, buat customer

class Pelanggan extends Pengguna
{
    protected $role = 'customer';

    public function getNamaRole()
    {
        return 'Pelanggan';
    }

    public function getHalamanDashboard()
    {
        return 'dashboard-customer.php';
    }

    // menu sidebar khusus pelanggan
    public function getMenu()
    {
        return [
            ['dashboard-customer.php', 'Dashboard',    'home'],
            ['order.php',              'Buat Pesanan', 'plus'],
            ['riwayat.php',            'Riwayat',      'clock'],
            ['profile.php',            'Profil',       'user'],
        ];
    }
}
