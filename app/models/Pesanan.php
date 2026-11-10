<?php
// ============================================================
// Pesanan.php  ->  1 baris tabel pesanan dalam bentuk OBJEK
//
// Dipakai bareng PDO::FETCH_CLASS (materi PDO Langkah 4, contoh class Student):
//   $stmt->fetchAll(PDO::FETCH_CLASS, 'Pesanan');
// Hasilnya setiap baris langsung jadi objek Pesanan, jadi bisa ditulis
//   $p->kode, $p->pelanggan, $p->total(), $p->sudahDitimbang()
//
// Nama properti di bawah HARUS sama dengan nama kolom hasil SELECT.
// ============================================================

class Pesanan
{
    // urutan status cucian dari awal sampai akhir
    const DAFTAR_STATUS = ['Menunggu Jemput', 'Diterima', 'Dicuci', 'Selesai', 'Diambil'];
    // status yang boleh dipilih admin setelah cucian ditimbang
    const STATUS_PROSES = ['Diterima', 'Dicuci', 'Selesai', 'Diambil'];

    // ----- kolom dari tabel pesanan -----
    public $id;
    public $kode;            // No. Nota, contoh: ORD6ab7da28cce8f
    public $user_id;
    public $nama_pelanggan;
    public $sumber;          // 'aplikasi' atau 'kios'
    public $layanan_id;
    public $nama_layanan;
    public $harga;
    public $satuan;
    public $berat;           // 0 = belum ditimbang
    public $catatan;         // alamat jemput / catatan
    public $status;
    public $promo_id;
    public $nama_promo;
    public $diskon;          // persen
    public $tanggal;

    // ----- kolom tambahan dari JOIN -----
    public $pelanggan;       // nama terbaru dari tabel users
    public $ulasan_rating;
    public $ulasan_komentar;

    // ---------- hitung-hitungan uang ----------

    // harga x berat (sebelum diskon)
    public function subtotal()
    {
        return (int)round($this->harga * $this->berat);
    }

    // potongan dari promo
    public function potongan()
    {
        return (int)round($this->subtotal() * $this->diskon / 100);
    }

    // yang harus dibayar
    public function total()
    {
        return $this->subtotal() - $this->potongan();
    }

    // ---------- cek kondisi ----------

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

    // ---------- buat ditampilin ----------

    public function asal()
    {
        return $this->sumber == 'aplikasi' ? 'Aplikasi' : 'Kios';
    }

    public function tanggalTampil($pakaiJam = true)
    {
        return tglPendek($this->tanggal, $pakaiJam);
    }

    // "2,5 kg" atau tulisan "Belum ditimbang"
    public function labelBerat()
    {
        if (!$this->sudahDitimbang()) {
            return '<span class="tag-tunggu">Belum ditimbang</span>';
        }
        return e(formatBerat($this->berat)) . ' ' . e($this->satuan);
    }

    // total bayar + label diskon kecil
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
