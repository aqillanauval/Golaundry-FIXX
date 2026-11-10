<?php
// ============================================================
// Pesanan.php  -  Satu baris tabel pesanan sebagai OBJEK.
//
// PesananModel mengambil data dengan PDO::FETCH_CLASS, 'Pesanan'
// sehingga setiap baris langsung menjadi objek kelas ini:
//   $p->kode, $p->pelanggan, $p->total(), $p->sudahDitimbang(), ...
//
// Nama properti = nama kolom hasil SELECT.
// Perhitungan uang ada di sini supaya angka di semua halaman selalu sama.
// ============================================================

class Pesanan
{
    // urutan status laundry
    public const DAFTAR_STATUS = ['Menunggu Jemput', 'Diterima', 'Dicuci', 'Selesai', 'Diambil'];
    // status yang boleh dipilih admin setelah cucian ditimbang
    public const STATUS_PROSES = ['Diterima', 'Dicuci', 'Selesai', 'Diambil'];

    // ----- kolom tabel pesanan -----
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
    // ----- kolom tambahan dari JOIN -----
    public $pelanggan;        // nama terbaru dari tabel users (atau nama pelanggan kios)
    public $ulasan_rating;
    public $ulasan_komentar;

    // ----- perhitungan -----

    public function berat(): float { return (float)$this->berat; }
    public function harga(): int { return (int)$this->harga; }
    public function diskon(): int { return (int)$this->diskon; }

    // harga x berat, sebelum diskon
    public function subtotal(): int
    {
        return (int)round($this->harga() * $this->berat());
    }

    public function potongan(): int
    {
        return (int)round($this->subtotal() * $this->diskon() / 100);
    }

    public function total(): int
    {
        return $this->subtotal() - $this->potongan();
    }

    // cucian dianggap sudah ditimbang kalau beratnya lebih dari 0
    public function sudahDitimbang(): bool
    {
        return $this->berat() > 0;
    }

    public function menungguJemput(): bool
    {
        return $this->status === 'Menunggu Jemput';
    }

    public function sudahSelesai(): bool
    {
        return $this->status === 'Selesai' || $this->status === 'Diambil';
    }

    public function punyaUlasan(): bool
    {
        return $this->ulasan_rating !== null;
    }

    public function asal(): string
    {
        return $this->sumber === 'aplikasi' ? 'Aplikasi' : 'Kios';
    }

    public function tanggalTampil(bool $denganJam = true): string
    {
        return tglPendek((string)$this->tanggal, $denganJam);
    }

    // ----- potongan HTML kecil untuk tabel -----

    public function labelBerat(): string
    {
        if (!$this->sudahDitimbang()) {
            return '<span class="tag-tunggu">Belum ditimbang</span>';
        }
        return e(formatBerat($this->berat)) . ' ' . e($this->satuan);
    }

    public function labelTotal(): string
    {
        if (!$this->sudahDitimbang()) {
            return '<span class="muted">Menunggu berat</span>';
        }
        $teks = '<strong>' . e(rupiah($this->total())) . '</strong>';
        if ($this->diskon() > 0) {
            $teks .= '<span class="tag-promo">Diskon ' . $this->diskon() . '%</span>';
        }
        return $teks;
    }
}
