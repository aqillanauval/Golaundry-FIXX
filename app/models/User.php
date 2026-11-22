<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    protected $table = 'users';

    // Tabel users cuma punya created_at, tidak ada updated_at
    const UPDATED_AT = null;

    // Fitur "remember me" bawaan Laravel tidak dipakai
    protected $rememberTokenName = '';

    protected $fillable = ['username', 'password', 'nama', 'role', 'karyawan_id'];

    // password tidak ikut ditampilkan kalau data user diubah ke array/JSON
    protected $hidden = ['password'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relasi: satu user punya banyak pesanan
    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'user_id');
    }

    // Relasi: akun staff adalah seorang karyawan (kosong untuk pelanggan)
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function isAdmin()
    {
        return $this->role == 'admin';
    }

    public function namaRole()
    {
        return $this->isAdmin() ? 'Staff' : 'Pelanggan';
    }

    // Menu sidebar sesuai role: [nama route, label, ikon Font Awesome, pola route aktif]
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

    // Hapus akun pelanggan.
    // Pesanan yang belum dijemput ikut dihapus, pesanan lain tetap disimpan
    // (user_id otomatis jadi NULL karena foreign key ON DELETE SET NULL).
    // Pakai transaksi database: kalau satu langkah gagal, semua dibatalkan.
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
