<?php
class Pesanan
{
    const DAFTAR_STATUS = ['Menunggu Jemput', 'Diterima', 'Dicuci', 'Selesai', 'Diambil'];
    const STATUS_PROSES = ['Diterima', 'Dicuci', 'Selesai', 'Diambil'];

    public $id;
    public $kode;            
    public $user_id;
    public $nama_pelanggan;
    public $sumber;          
    public $layanan_id;
    public $nama_layanan;
    public $harga;
    public $satuan;
    public $berat;       
    public $catatan;      
    public $status;
    public $promo_id;
    public $nama_promo;
    public $diskon;       
    public $tanggal;

    public $pelanggan;     
    public $ulasan_rating;
    public $ulasan_komentar;

    public function subtotal()
    {
        return (int)round($this->harga * $this->berat);
    }

    public function potongan()
    {
        return (int)round($this->subtotal() * $this->diskon / 100);
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

    public function punyaUlasan()
    {
        return $this->ulasan_rating !== null;
    }

    public function punyaDiskon()
    {
        return $this->diskon > 0;
    }

    public function asal()
    {
        return $this->sumber == 'aplikasi' ? 'Aplikasi' : 'Kios';
    }

    public function tanggalTampil($pakaiJam = true)
    {
        return tglPendek($this->tanggal, $pakaiJam);
    }

    public function labelBerat()
    {
        if (!$this->sudahDitimbang()) {
            return '<span class="tag-tunggu">Belum ditimbang</span>';
        }
        return e(formatBerat($this->berat)) . ' ' . e($this->satuan);
    }

    public function labelTotal()
    {
        if (!$this->sudahDitimbang()) {
            return '<span class="muted">Menunggu berat</span>';
        }
        $html = '<strong>' . e(rupiah($this->total())) . '</strong>';
        if ($this->punyaDiskon()) {
            $html .= '<span class="tag-promo">Diskon ' . (int)$this->diskon . '%</span>';
        }
        return $html;
    }
}