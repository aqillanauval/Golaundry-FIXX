<?php
// Pelanggan.php  -  turunan Pengguna untuk customer

class Pelanggan extends Pengguna
{
    protected string $role = 'customer';

    public function namaRole(): string
    {
        return 'Pelanggan';
    }

    public function urlDashboard(): string
    {
        return 'dashboard-customer.php';
    }

    public function menu(): array
    {
        return [
            ['dashboard-customer.php', 'Dashboard',    'home'],
            ['order.php',              'Buat Pesanan', 'plus'],
            ['riwayat.php',            'Riwayat',      'clock'],
            ['profile.php',            'Profil',       'user'],
        ];
    }
}
