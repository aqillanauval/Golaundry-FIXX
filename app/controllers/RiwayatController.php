<?php
// ============================================================
// RiwayatController.php  -  Riwayat pesanan pelanggan + CRUD Ulasan
// Pesanan yang sudah Selesai / Diambil boleh diberi ulasan.
// ============================================================

class RiwayatController extends Controller
{
    public function invoke(): void
    {
        $saya = $this->wajibLogin('customer');
        $pesanan = new PesananModel();
        $ulasan = new UlasanModel();
        $error = '';
        $formUlasan = null; // kalau tidak null, kartu form ulasan ditampilkan

        if ($this->isPost()) {
            $kode = $this->post('id');
            $o = $pesanan->cariMilikUser($kode, $saya->getId());
            if (!$o || !$o->sudahSelesai()) {
                $this->flash('error', 'Pesanan tidak ditemukan atau belum selesai.');
                $this->redirect('riwayat.php');
            }

            // CREATE & UPDATE ulasan
            if ($this->aksi() === 'simpan_ulasan') {
                $rating = (int)$this->post('rating');
                $komentar = bersihkan($this->post('komentar'), 500);

                if ($rating < 1 || $rating > 5) {
                    $error = 'Pilih rating 1 sampai 5 bintang ya.';
                } elseif ($komentar === '') {
                    $error = 'Komentar wajib diisi.';
                } else {
                    $ulasan->simpan((int)$o->id, $rating, $komentar);
                    Logger::catat('Simpan ulasan', "$kode ($rating bintang)");
                    $this->flash('sukses', 'Terima kasih! Ulasanmu sudah tersimpan.');
                    $this->redirect('riwayat.php');
                }
                $formUlasan = ['id' => $kode, 'rating' => $rating, 'komentar' => $komentar];
            }

            // DELETE ulasan
            if ($this->aksi() === 'hapus') {
                $ulasan->hapusUntukPesanan((int)$o->id);
                Logger::catat('Hapus ulasan', $kode);
                $this->flash('sukses', 'Ulasan berhasil dihapus.');
                $this->redirect('riwayat.php');
            }
        }

        // klik "Beri Ulasan" / "Edit" -> riwayat.php?ulas=KODE
        if ($formUlasan === null && $this->get('ulas') !== '') {
            $o = $pesanan->cariMilikUser($this->get('ulas'), $saya->getId());
            if ($o && $o->sudahSelesai()) {
                $formUlasan = [
                    'id'       => $o->kode,
                    'rating'   => $o->punyaUlasan() ? (int)$o->ulasan_rating : 5,
                    'komentar' => $o->punyaUlasan() ? $o->ulasan_komentar : '',
                ];
            }
        }

        $this->view('riwayat/index', [
            'judulHalaman' => 'Riwayat Transaksi',
            'subJudul'     => 'Semua pesanan laundry kamu. Klik No. Nota untuk lihat / cetak nota.',
            'jsTambahan'   => ['riwayat.js'],
            'error'        => $error,
            'formUlasan'   => $formUlasan,
            'pesananSaya'  => $pesanan->milikUser($saya->getId()),
        ]);
    }
}
