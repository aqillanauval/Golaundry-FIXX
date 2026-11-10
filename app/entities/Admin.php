<?php
// Admin.php  -  turunan Pengguna untuk admin / staff laundry

class Admin extends Pengguna
{
    protected string $role = 'admin';

    public function namaRole(): string
    {
        return 'Admin / Staff';
    }

    public function urlDashboard(): string
    {
        return 'dashboard-admin.php';
    }

    public function menu(): array
    {
        return [
            ['dashboard-admin.php', 'Dashboard',     'home'],
            ['transaksi.php',       'Transaksi',     'file'],
            ['layanan.php',         'Layanan',       'tag'],
            ['promo.php',           'Promo',         'gift'],
            ['pelanggan.php',       'Pelanggan',     'users'],
            ['karyawan.php',        'Karyawan',      'briefcase'],
            ['log.php',             'Log Aktivitas', 'list'],
            ['profile.php',         'Profil',        'user'],
        ];
    }
}
