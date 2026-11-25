<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawan';
    public $timestamps = false;

    protected $fillable = ['nama', 'jabatan', 'no_hp'];

    const SARAN_JABATAN = ['Kurir', 'Kasir', 'Operator Cuci', 'Setrika', 'Admin Toko'];

    // Pesanan yang dijemput karyawan ini (hanya berlaku untuk kurir)
    public function pesananDijemput()
    {
        return $this->hasMany(Pesanan::class, 'kurir_id');
    }

    // Akun staff milik karyawan ini (kosong kalau tidak punya akses login)
    public function akun()
    {
        return $this->hasOne(User::class, 'karyawan_id');
    }

    public function isKurir()
    {
        return strtolower(trim($this->jabatan)) == 'kurir';
    }

    // Karyawan berjabatan Kurir, untuk pilihan di form isi berat
    public static function daftarKurir()
    {
        return self::whereRaw('LOWER(TRIM(jabatan)) = ?', ['kurir'])->orderBy('nama')->get();
    }
}
