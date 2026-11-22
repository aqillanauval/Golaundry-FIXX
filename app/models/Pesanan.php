<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';
    public $timestamps = false;

    // Urutan status laundry GOLaundry
    const DAFTAR_STATUS = ['Menunggu Jemput', 'Diterima', 'Dicuci', 'Selesai', 'Diambil'];

    // Status yang boleh dipilih staff setelah cucian ditimbang
    const STATUS_PROSES = ['Diterima', 'Dicuci', 'Selesai', 'Diambil'];

    protected $fillable = [
        'kode', 'user_id', 'nama_pelanggan', 'sumber',
        'layanan_id', 'nama_layanan', 'harga', 'satuan',
        'berat', 'catatan', 'status', 'kurir_id',
        'promo_id', 'nama_promo', 'diskon', 'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'berat'   => 'float',
        'harga'   => 'integer',
        'diskon'  => 'integer',
    ];

    // Di URL, pesanan dicari pakai kode (contoh: /nota/ORD6ab7da28cce8f), bukan id
    public function getRouteKeyName()
    {
        return 'kode';
    }

    // ---------- Relasi ----------

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    public function promo()
    {
        return $this->belongsTo(Promo::class, 'promo_id');
    }

    public function ulasan()
    {
        return $this->hasOne(Ulasan::class, 'pesanan_id');
    }

    // Kurir (karyawan) yang menjemput cucian. Kosong untuk pesanan kios.
    public function kurir()
    {
        return $this->belongsTo(Karyawan::class, 'kurir_id');
    }

    // ---------- Fungsi bantu ----------

    public static function buatKode()
    {
        return uniqid('ORD'); // contoh: ORD6ab7da28cce8f
    }

    // Nama pelanggan: ambil dari akun kalau masih ada, kalau tidak pakai nama yang disimpan
    public function namaPelanggan()
    {
        if ($this->user) {
            return $this->user->nama;
        }
        return $this->nama_pelanggan;
    }

    public function subtotal()
    {
        return (int) round($this->harga * $this->berat);
    }

    public function potongan()
    {
        return (int) round($this->subtotal() * $this->diskon / 100);
    }

    public function total()
    {
        return $this->subtotal() - $this->potongan();
    }

    public function sudahDitimbang()
    {
        return $this->berat > 0;
    }

    public function menungguJemput()
    {
        return $this->status == 'Menunggu Jemput';
    }

    public function sudahSelesai()
    {
        return $this->status == 'Selesai' || $this->status == 'Diambil';
    }

    public function punyaDiskon()
    {
        return $this->diskon > 0;
    }

    public function asal()
    {
        return $this->sumber == 'aplikasi' ? 'Aplikasi' : 'Kios';
    }

    // Posisi status di DAFTAR_STATUS (0 - 4), dipakai untuk tracker
    public function langkahStatus()
    {
        return array_search($this->status, self::DAFTAR_STATUS);
    }
}
