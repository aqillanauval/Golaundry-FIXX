<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    protected $table = 'users';

    const UPDATED_AT = null;

    protected $rememberTokenName = '';

    protected $fillable = ['username', 'password', 'nama', 'role'];

    protected $hidden = ['password'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'user_id');
    }

    public function isAdmin()
    {
        return $this->role == 'admin';
    }

    public function namaRole()
    {
        return $this->isAdmin() ? 'Staff' : 'Pelanggan';
    }

    public function menu()
    {
        if ($this->isAdmin()) {
            return [
                'Utama' => [
                    ['dashboard', 'Dashboard', 'fa-house', 'dashboard'],
                    ['transaksi.index', 'Transaksi', 'fa-receipt', 'transaksi.*'],
                    ['ulasan.index', 'Ulasan', 'fa-star', 'ulasan.*'],
                ],
                'Kelola Data' => [
                    ['layanan.index', 'Layanan', 'fa-shirt', 'layanan.*'],
                    ['promo.index', 'Promo', 'fa-gift', 'promo.*'],
                    ['pelanggan.index', 'Pelanggan', 'fa-users', 'pelanggan.*'],
                    ['karyawan.index', 'Karyawan', 'fa-id-badge', 'karyawan.*'],
                ],
                'Lainnya' => [
                                        ['profil', 'Profil Saya', 'fa-user', 'profil*'],
                ],
            ];
        }

        return [
            'Utama' => [
                ['dashboard', 'Dashboard', 'fa-house', 'dashboard'],
                ['pesanan.index', 'Buat Pesanan', 'fa-circle-plus', 'pesanan.*'],
                ['riwayat.index', 'Riwayat Pesanan', 'fa-clock-rotate-left', 'riwayat.*'],
            ],
            'Akun' => [
                ['profil', 'Profil Saya', 'fa-user', 'profil*'],
            ],
        ];
    }

    public function hapusAkunPelanggan()
    {
        if ($this->isAdmin()) {
            return false;
        }

        DB::transaction(function () {
            Pesanan::where('user_id', $this->id)
                ->where('status', 'Menunggu Jemput')
                ->delete();

            $this->delete();
        });

        return true;
    }
}
