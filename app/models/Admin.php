<?php

class Admin extends Pengguna
{
    protected $role = 'admin';

    public function getNamaRole()
    {
        return 'Admin / Staff';
    }

    public function getHalamanDashboard()
    {
        return 'dashboard-admin.php';
    }

    public function getMenu()
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
