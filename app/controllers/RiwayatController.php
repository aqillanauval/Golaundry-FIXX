<?php
// ============================================================
// RiwayatController.php  ->  riwayat pesanan pelanggan + CRUD Ulasan
// Pesanan yang udah "Selesai" / "Diambil" boleh dikasih ulasan.
// ============================================================

class RiwayatController extends Controller
{
    public function invoke()
    {
        $saya = $this->harusLogin('customer');
        $pesananModel = new PesananModel();
        $ulasanModel = new UlasanModel();

        $error = '';
        $formUlasan = null; // kalau nggak null, form ulasan ditampilin

        if ($this->adaPost()) {
            $kode = $this->post('id');
            $pesanan = $pesananModel->cariMilikUser($kode, $saya->getId());
            if (!$pesanan || !$pesanan->sudahSelesai()) {
                $this->pesan('error', 'Pesanan nggak ketemu atau belum selesai.');
                $this->pindahKe('riwayat.php');
            }

            // ----- CREATE & UPDATE ulasan -----
            if ($this->post('aksi') == 'simpan_ulasan') {
                $rating = (int)$this->post('rating');
                $komentar = bersihkan($this->post('komentar'), 500);

                if ($rating < 1 || $rating > 5) {
                    $error = 'Pilih bintang 1 sampai 5 ya.';
                } elseif ($komentar == '') {
                    $error = 'Komentarnya wajib diisi.';
                } else {
                    $ulasanModel->simpan($pesanan->id, $rating, $komentar);
                    catatLog('Simpan ulasan', "$kode ($rating bintang)");
                    $this->pesan('sukses', 'Makasih! Ulasanmu udah disimpan.');
                    $this->pindahKe('riwayat.php');
                }
                $formUlasan = ['id' => $kode, 'rating' => $rating, 'komentar' => $komentar];
            }

            // ----- DELETE ulasan -----
            if ($this->post('aksi') == 'hapus') {
                $ulasanModel->hapusByPesanan($pesanan->id);
                catatLog('Hapus ulasan', $kode);
                $this->pesan('sukses', 'Ulasan berhasil dihapus.');
                $this->pindahKe('riwayat.php');
            }
        }

        // klik "Beri Ulasan" / "Edit" -> riwayat.php?ulas=KODE
        if ($formUlasan == null && $this->get('ulas') != '') {
            $pesanan = $pesananModel->cariMilikUser($this->get('ulas'), $saya->getId());
            if ($pesanan && $pesanan->sudahSelesai()) {
                $formUlasan = [
                    'id'       => $pesanan->kode,
                    'rating'   => $pesanan->punyaUlasan() ? $pesanan->ulasan_rating : 5,
                    'komentar' => $pesanan->punyaUlasan() ? $pesanan->ulasan_komentar : '',
                ];
            }
        }

        $this->tampilkan('riwayat/index', [
            'judulHalaman' => 'Riwayat',
            'subJudul'     => 'Semua pesanan laundry kamu. Klik No. Nota buat liat / cetak nota.',
            'jsTambahan'   => ['riwayat.js'],
            'error'        => $error,
            'formUlasan'   => $formUlasan,
            'pesananSaya'  => $pesananModel->milikUser($saya->getId()),
        ]);
    }
}
