<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $table = 'promo';
    public $timestamps = false;

    protected $fillable = ['judul', 'deskripsi', 'diskon', 'berlaku_sampai', 'foto'];

    protected $casts = [
        'berlaku_sampai' => 'date',
    ];

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'promo_id');
    }

    public static function yangAktif()
    {
        return self::where('berlaku_sampai', '>=', date('Y-m-d'))
            ->orderBy('berlaku_sampai')
            ->get();
    }

    public static function cariAktif($id)
    {
        return self::where('id', $id)
            ->where('berlaku_sampai', '>=', date('Y-m-d'))
            ->first();
    }

    public function masihBerlaku()
    {
        return $this->berlaku_sampai->format('Y-m-d') >= date('Y-m-d');
    }

    public function adaFoto()
    {
        return $this->foto != '' && file_exists(public_path('uploads/promo/' . $this->foto));
    }

    public function urlFoto()
    {
        return asset('uploads/promo/' . $this->foto);
    }
}
