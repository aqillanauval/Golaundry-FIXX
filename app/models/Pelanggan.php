<?php
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
