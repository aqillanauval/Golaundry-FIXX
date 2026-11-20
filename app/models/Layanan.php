<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    // Nama tabel bahasa Indonesia, jadi ditulis manual
    // (kalau tidak, Laravel akan mencari tabel "layanans")
    protected $table = 'layanan';

    // Tabel ini tidak punya kolom created_at & updated_at
    public $timestamps = false;

    protected $fillable = ['nama', 'harga', 'satuan'];

    const DAFTAR_SATUAN = ['kg', 'item', 'pasang'];

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'layanan_id');
    }
}
