<?php
// ============================================================
// DashboardAdminController.php
// Admin ngurus 2 jalur pesanan:
//   1. Dari aplikasi : kurir jemput & nimbang, terus admin isi beratnya di sini
//   2. Di kios       : pelanggan dateng langsung, dicatat di menu Transaksi
// Status pesanan bisa diganti lewat dropdown tanpa reload (AJAX -> balasan JSON)
// ============================================================

class DashboardAdminController extends Controller
{
    private $pesananModel;

    public function __construct()
    {
        $this->pesananModel = new PesananModel();
    }

    public function invoke()
    {
        $this->harusLogin('admin');

        if ($this->adaPost() && $this->post('aksi') == 'ubah_status') {
            $this->ubahStatus(); // AJAX
        }
        if ($this->adaPost() && $this->post('aksi') == 'isi_berat') {
            $this->isiBerat();
        }

        // hitung pendapatan hari ini (yang belum ditimbang totalnya masih 0)
        $pesananHariIni = $this->pesananModel->hariIni();
        $pendapatan = 0;
        foreach ($pesananHariIni as $p) {
            $pendapatan += $p->total();
        }

        $userModel = new UserModel();

        $this->tampilkan('dashboard/admin', [
            'judulHalaman'      => 'Dashboard',
            'subJudul'          => 'Ringkasan laundry hari ini.',
            'jsTambahan'        => ['dashboard-admin.js'],
            'sapa'              => sapaan(),
            'jumlahHariIni'     => count($pesananHariIni),
            'pendapatanHariIni' => $pendapatan,
            'sedangDiproses'    => $this->pesananModel->jumlahDiproses(),
            'jumlahPelanggan'   => $userModel->jumlahPelanggan(),
            'perluDijemput'     => $this->pesananModel->menungguJemput(),
            'berjalan'          => $this->pesananModel->sudahDitimbang(),
            'statusProses'      => Pesanan::STATUS_PROSES,
        ]);
    }

    // dipanggil JavaScript (fetch) dari dashboard-admin.js, balasannya JSON
    private function ubahStatus()
    {
        $kode = $this->post('id');
        $status = $this->post('status');

        if (!in_array($status, Pesanan::STATUS_PROSES)) {
            $this->kirimJson(['sukses' => false, 'pesan' => 'Status nggak valid.']);
        }

        $pesanan = $this->pesananModel->cariByKode($kode);
        if (!$pesanan) {
            $this->kirimJson(['sukses' => false, 'pesan' => 'Pesanan nggak ketemu.']);
        }
        if (!$pesanan->sudahDitimbang()) {
            $this->kirimJson(['sukses' => false, 'pesan' => 'Isi berat cuciannya dulu ya.']);
        }

        $this->pesananModel->ubahStatus($kode, $status);
        catatLog('Ubah status', "$kode -> $status");
        $this->kirimJson(['sukses' => true, 'pesan' => 'Status diganti jadi ' . $status . '.']);
    }

    private function isiBerat()
    {
        $kode = $this->post('id');
        $berat = round((float)str_replace(',', '.', $this->post('berat')), 1); // "2,5" juga boleh

        if ($berat <= 0 || $berat > 500) {
            $this->pesan('error', 'Berat harus lebih dari 0 dan masuk akal (maks. 500).');
        } elseif ($this->pesananModel->isiBerat($kode, $berat)) {
            $p = $this->pesananModel->cariByKode($kode);
            catatLog('Isi berat', "$kode: $berat {$p->satuan}");
            $this->pesan('sukses', 'Berat ' . formatBerat($berat) . ' ' . $p->satuan . ' buat ' . $p->pelanggan
                . ' udah disimpan. Total bayar ' . rupiah($p->total()) . '.');
        } else {
            $this->pesan('error', 'Pesanan nggak ketemu atau beratnya udah diisi.');
        }
        $this->pindahKe('dashboard-admin.php');
    }
}
