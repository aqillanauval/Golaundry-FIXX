<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{

    protected $table = 'layanan';

    public $timestamps = false;

    protected $fillable = ['nama', 'harga', 'satuan'];

    const DAFTAR_SATUAN = ['kg', 'item', 'pasang'];

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'layanan_id');
    }
}
