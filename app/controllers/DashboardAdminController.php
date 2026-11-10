<?php
// ============================================================
// DashboardAdminController.php
// Admin menangani 2 jalur pesanan:
//   1. Dari aplikasi : kurir menjemput, menimbang, lalu admin mengisi berat
//   2. Di kios       : dicatat langsung di menu Transaksi
// Status pesanan bisa diubah lewat dropdown tanpa reload (AJAX -> JSON).
// ============================================================

class DashboardAdminController extends Controller
{
    private PesananModel $pesanan;

    public function __construct()
    {
        $this->pesanan = new PesananModel();
    }

    public function invoke(): void
    {
        $this->wajibLogin('admin');

        if ($this->isPost() && $this->aksi() === 'ubah_status') {
            $this->ubahStatusAjax();
        }
        if ($this->isPost() && $this->aksi() === 'isi_berat') {
            $this->isiBerat();
        }

        // ----- angka ringkasan -----
        $hariIni = $this->pesanan->hariIni();
        $pendapatanHariIni = 0;
        foreach ($hariIni as $o) {
            $pendapatanHariIni += $o->total(); // sudah dipotong diskon, 0 kalau belum ditimbang
        }

        $this->view('dashboard/admin', [
            'judulHalaman'      => 'Dashboard',
            'subJudul'          => 'Ringkasan operasional laundry hari ini.',
            'jsTambahan'        => ['dashboard-admin.js'],
            'sapa'              => sapaan(),
            'pesananHariIni'    => count($hariIni),
            'pendapatanHariIni' => $pendapatanHariIni,
            'sedangDiproses'    => $this->pesanan->jumlahDiproses(),
            'jumlahPelanggan'   => (new UserModel())->jumlahPelanggan(),
            'perluDijemput'     => $this->pesanan->menungguJemput(),
            'berjalan'          => $this->pesanan->sudahDitimbang(8),
            'statusProses'      => Pesanan::STATUS_PROSES,
        ]);
    }

    // dipanggil JavaScript (fetch), balasannya JSON
    private function ubahStatusAjax(): void
    {
        $kode = $this->post('id');
        $status = $this->post('status');

        if (!in_array($status, Pesanan::STATUS_PROSES, true)) {
            $this->json(['sukses' => false, 'pesan' => 'Status tidak valid.']);
        }
        $p = $this->pesanan->cariByKode($kode);
        if (!$p) {
            $this->json(['sukses' => false, 'pesan' => 'Pesanan tidak ditemukan.']);
        }
        if (!$p->sudahDitimbang()) {
            $this->json(['sukses' => false, 'pesan' => 'Isi berat cucian dulu sebelum mengubah status.']);
        }
        $this->pesanan->ubahStatus($kode, $status);
        Logger::catat('Ubah status', "$kode -> $status");
        $this->json(['sukses' => true, 'pesan' => 'Status diubah menjadi ' . $status . '.']);
    }

    private function isiBerat(): void
    {
        $kode = $this->post('id');
        $berat = round((float)str_replace(',', '.', $this->post('berat')), 1); // "2,5" juga boleh

        if ($berat <= 0 || $berat > 500) {
            $this->flash('error', 'Berat harus lebih dari 0 dan masuk akal (maksimal 500).');
        } elseif ($this->pesanan->isiBerat($kode, $berat)) {
            $p = $this->pesanan->cariByKode($kode);
            Logger::catat('Isi berat', "$kode: $berat {$p->satuan}");
            $this->flash('sukses', 'Berat ' . formatBerat($berat) . ' ' . $p->satuan . ' tersimpan untuk ' . $p->pelanggan
                . '. Total bayar ' . rupiah($p->total()) . '.');
        } else {
            $this->flash('error', 'Pesanan tidak ditemukan atau beratnya sudah diisi.');
        }
        $this->redirect('dashboard-admin.php');
    }
}
