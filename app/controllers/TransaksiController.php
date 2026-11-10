<?php
// ============================================================
// TransaksiController.php  ->  CRUD Transaksi (khusus admin)
//   1. Catat pelanggan yang dateng langsung ke kios (berat langsung diisi)
//   2. Liat, cari, edit, hapus semua transaksi
//      (pesanan aplikasi yang belum ditimbang juga bisa diisi beratnya di sini)
// Fitur cari (transaksi.php?cari=...) pakai LIKE + prepared statement.
// ============================================================

class TransaksiController extends Controller
{
    private $pesananModel;
    private $layananModel;
    private $promoModel;

    public function __construct()
    {
        $this->pesananModel = new PesananModel();
        $this->layananModel = new LayananModel();
        $this->promoModel = new PromoModel();
    }

    public function invoke()
    {
        $this->harusLogin('admin');

        $error = '';
        $form = ['id' => '', 'nama_pelanggan' => '', 'layanan' => '', 'berat' => '', 'status' => 'Diterima', 'catatan' => '', 'promo' => ''];

        // ----- CREATE & UPDATE -----
        if ($this->adaPost() && $this->post('aksi') == 'simpan') {
            $form['id'] = $this->post('id');
            $form['nama_pelanggan'] = bersihkan($this->post('nama_pelanggan'), 100);
            $form['layanan'] = $this->post('layanan');
            $form['berat'] = round((float)str_replace(',', '.', $this->post('berat')), 1);
            $form['status'] = $this->post('status') != '' ? $this->post('status') : 'Diterima';
            $form['catatan'] = bersihkan($this->post('catatan'), 500);
            $form['promo'] = $this->post('promo');

            $layanan = null;
            if ($form['layanan'] != '') {
                $layanan = $this->layananModel->cari($form['layanan']);
            }

            $lagiEdit = $form['id'] != '';
            $lama = null;
            if ($lagiEdit) {
                $lama = $this->pesananModel->cariByKode($form['id']);
            }
            $masihMenunggu = $lama && $lama->menungguJemput();

            // pesanan aplikasi yang belum ditimbang: begitu berat diisi, status otomatis "Diterima"
            if ($masihMenunggu) {
                $form['status'] = 'Diterima';
            }

            // promo cuma bisa dipilih waktu bikin transaksi baru
            $promo = null;
            $errorPromo = '';
            if (!$lagiEdit && $form['promo'] != '') {
                $promo = $this->promoModel->cariAktif($form['promo']);
                if (!$promo) {
                    $errorPromo = 'Promo nggak valid atau udah habis masa berlakunya.';
                }
            }

            if ($lagiEdit && !$lama) {
                $error = 'Transaksi nggak ketemu.';
            } elseif (!$lagiEdit && $form['nama_pelanggan'] == '') {
                $error = 'Nama pelanggan wajib diisi.';
            } elseif (!$layanan) {
                $error = 'Pilih layanan yang bener ya.';
            } elseif ($form['berat'] <= 0 || $form['berat'] > 500) {
                $error = 'Berat harus lebih dari 0 dan masuk akal (maks. 500).';
            } elseif (!in_array($form['status'], Pesanan::STATUS_PROSES)) {
                $error = 'Status nggak valid.';
            } elseif ($errorPromo != '') {
                $error = $errorPromo;
            } elseif (!$lagiEdit) {
                // CREATE (pelanggan kios)
                $kode = $this->pesananModel->buat([
                    'user_id'        => null,
                    'nama_pelanggan' => $form['nama_pelanggan'],
                    'sumber'         => 'kios',
                    'layanan'        => $layanan,
                    'berat'          => $form['berat'],
                    'catatan'        => $form['catatan'],
                    'status'         => $form['status'],
                    'promo'          => $promo,
                ]);
                catatLog('Catat transaksi kios', $kode . ' ' . $form['nama_pelanggan']);
                $this->pesan('sukses', 'Transaksi pelanggan kios berhasil dicatat.');
                $this->pindahKe('transaksi.php');
            } else {
                // UPDATE
                $this->pesananModel->ubahOlehAdmin($form['id'], $layanan, $form['berat'], $form['status'], $form['catatan']);
                catatLog($masihMenunggu ? 'Isi berat' : 'Ubah transaksi', $form['id']);
                $this->pesan('sukses', $masihMenunggu ? 'Berat disimpan, pesanan sekarang berstatus Diterima.' : 'Transaksi berhasil diubah.');
                $this->pindahKe('transaksi.php');
            }
        }

        // ----- DELETE -----
        if ($this->adaPost() && $this->post('aksi') == 'hapus') {
            $kode = $this->post('id');
            if ($this->pesananModel->hapusByKode($kode)) {
                catatLog('Hapus transaksi', $kode);
                $this->pesan('sukses', 'Transaksi berhasil dihapus.');
            }
            $this->pindahKe('transaksi.php');
        }

        // ----- mode edit: transaksi.php?edit=KODE -----
        if ($this->get('edit') != '' && $error == '') {
            $p = $this->pesananModel->cariByKode($this->get('edit'));
            if ($p) {
                $form = [
                    'id'             => $p->kode,
                    'nama_pelanggan' => $p->pelanggan,
                    'layanan'        => (string)$p->layanan_id,
                    'berat'          => $p->sudahDitimbang() ? (float)$p->berat : '',
                    'status'         => $p->status,
                    'catatan'        => $p->catatan,
                    'promo'          => '',
                ];
            }
        }

        // data pesanan yang lagi diedit (buat info promo & mode "isi berat")
        $diedit = null;
        if ($form['id'] != '') {
            $diedit = $this->pesananModel->cariByKode($form['id']);
            if ($diedit && $form['nama_pelanggan'] == '') {
                $form['nama_pelanggan'] = $diedit->pelanggan;
            }
        }

        // ----- daftar transaksi + pencarian -----
        $cari = bersihkan($this->get('cari'), 100);
        $semua = $this->pesananModel->semua($cari);

        $totalPendapatan = 0;
        $jumlahMenunggu = 0;
        foreach ($semua as $p) {
            $totalPendapatan += $p->total();
            if ($p->menungguJemput()) {
                $jumlahMenunggu++;
            }
        }

        $this->tampilkan('transaksi/index', [
            'judulHalaman'    => 'Transaksi',
            'subJudul'        => 'Catat pelanggan kios & pantau semua transaksi. Klik No. Nota buat liat / cetak nota.',
            'jsTambahan'      => ['estimasi.js', 'transaksi.js'],
            'error'           => $error,
            'form'            => $form,
            'modeEdit'        => $form['id'] != '',
            'editMenunggu'    => $diedit && $diedit->menungguJemput(),
            'diskonTetap'     => $diedit ? (int)$diedit->diskon : 0,
            'namaPromoTetap'  => $diedit ? $diedit->nama_promo : '',
            'layananList'     => $this->layananModel->semua(),
            'promoAktif'      => $this->promoModel->aktif(),
            'statusProses'    => Pesanan::STATUS_PROSES,
            'semua'           => $semua,
            'cari'            => $cari,
            'totalPendapatan' => $totalPendapatan,
            'jumlahMenunggu'  => $jumlahMenunggu,
        ]);
    }
}
