<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawan';
    public $timestamps = false;

    protected $fillable = ['nama', 'jabatan', 'no_hp'];

    const SARAN_JABATAN = ['Kurir', 'Kasir', 'Operator Cuci', 'Setrika', 'Admin Toko'];
}
