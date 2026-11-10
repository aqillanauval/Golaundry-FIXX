<?php
// ============================================================
// TransaksiController.php  -  CRUD Transaksi (khusus admin)
//   1. Mencatat pelanggan yang datang langsung ke kios (berat langsung diisi)
//   2. Melihat, mencari, mengedit, dan menghapus semua transaksi.
//      Pesanan aplikasi yang belum ditimbang bisa diisi beratnya di sini.
// Pencarian (?cari=...) memakai LIKE dengan prepared statement.
// ============================================================

class TransaksiController extends Controller
{
    private PesananModel $pesanan;
    private LayananModel $layanan;
    private PromoModel $promo;

    public function __construct()
    {
        $this->pesanan = new PesananModel();
        $this->layanan = new LayananModel();
        $this->promo = new PromoModel();
    }

    public function invoke(): void
    {
        $this->wajibLogin('admin');

        $error = '';
        $form = ['id' => '', 'nama_pelanggan' => '', 'layanan' => '', 'qty' => '', 'status' => 'Diterima', 'catatan' => '', 'promo' => ''];

        // ----- CREATE & UPDATE -----
        if ($this->isPost() && $this->aksi() === 'simpan') {
            $form = [
                'id'             => $this->post('id'),
                'nama_pelanggan' => bersihkan($this->post('nama_pelanggan'), 100),
                'layanan'        => $this->post('layanan'),
                'qty'            => round((float)str_replace(',', '.', $this->post('qty', '0')), 1),
                'status'         => $this->post('status', 'Diterima'),
                'catatan'        => bersihkan($this->post('catatan'), 500),
                'promo'          => $this->post('promo'),
            ];
            $layananDipilih = $form['layanan'] !== '' ? $this->layanan->cari((int)$form['layanan']) : null;
            $sedangEdit = ($form['id'] !== '');
            $lama = $sedangEdit ? $this->pesanan->cariByKode($form['id']) : null;
            $masihMenunggu = $lama && $lama->menungguJemput();

            // pesanan aplikasi yang belum ditimbang: begitu berat diisi, status jadi Diterima
            if ($masihMenunggu) {
                $form['status'] = 'Diterima';
            }

            // promo hanya dipilih saat membuat transaksi baru
            $promo = null;
            $promoError = '';
            if (!$sedangEdit && $form['promo'] !== '') {
                $promo = $this->promo->cariAktif((int)$form['promo']);
                if (!$promo) {
                    $promoError = 'Promo tidak valid atau sudah berakhir.';
                }
            }

            if ($sedangEdit && !$lama) {
                $error = 'Transaksi tidak ditemukan.';
            } elseif (!$sedangEdit && $form['nama_pelanggan'] === '') {
                $error = 'Nama pelanggan wajib diisi.';
            } elseif (!$layananDipilih) {
                $error = 'Silakan pilih layanan yang valid.';
            } elseif ($form['qty'] <= 0 || $form['qty'] > 500) {
                $error = 'Berat harus lebih dari 0 dan masuk akal (maksimal 500).';
            } elseif (!in_array($form['status'], Pesanan::STATUS_PROSES, true)) {
                $error = 'Status tidak valid.';
            } elseif ($promoError !== '') {
                $error = $promoError;
            } elseif (!$sedangEdit) {
                $kode = $this->pesanan->buat([
                    'user_id'        => null,
                    'nama_pelanggan' => $form['nama_pelanggan'],
                    'sumber'         => 'kios',
                    'layanan'        => $layananDipilih,
                    'berat'          => $form['qty'],
                    'catatan'        => $form['catatan'],
                    'status'         => $form['status'],
                    'promo'          => $promo,
                ]);
                Logger::catat('Catat transaksi kios', $kode . ' ' . $form['nama_pelanggan']);
                $this->flash('sukses', 'Transaksi pelanggan kios berhasil dicatat.');
                $this->redirect('transaksi.php');
            } else {
                $this->pesanan->ubahOlehAdmin($form['id'], $layananDipilih, $form['qty'], $form['status'], $form['catatan']);
                Logger::catat($masihMenunggu ? 'Isi berat' : 'Ubah transaksi', $form['id']);
                $this->flash('sukses', $masihMenunggu ? 'Berat tersimpan. Pesanan sekarang berstatus Diterima.' : 'Transaksi berhasil diubah.');
                $this->redirect('transaksi.php');
            }
        }

        // ----- DELETE -----
        if ($this->isPost() && $this->aksi() === 'hapus') {
            $kode = $this->post('id');
            if ($this->pesanan->hapusByKode($kode)) {
                Logger::catat('Hapus transaksi', $kode);
                $this->flash('sukses', 'Transaksi berhasil dihapus.');
            }
            $this->redirect('transaksi.php');
        }

        // ----- mode edit: ?edit=KODE -----
        if ($this->get('edit') !== '' && $error === '') {
            $o = $this->pesanan->cariByKode($this->get('edit'));
            if ($o) {
                $form = [
                    'id' => $o->kode, 'nama_pelanggan' => $o->pelanggan, 'layanan' => (string)$o->layanan_id,
                    'qty' => $o->sudahDitimbang() ? formatBerat($o->berat) : '', 'status' => $o->status,
                    'catatan' => $o->catatan, 'promo' => '',
                ];
            }
        }
        $diedit = $form['id'] !== '' ? $this->pesanan->cariByKode($form['id']) : null;
        if ($diedit && $form['nama_pelanggan'] === '') {
            $form['nama_pelanggan'] = $diedit->pelanggan;
        }

        // ----- daftar + pencarian -----
        $cari = bersihkan($this->get('cari'), 100);
        $semua = $this->pesanan->semua($cari);
        $totalPendapatan = 0;
        $jumlahMenunggu = 0;
        foreach ($semua as $o) {
            $totalPendapatan += $o->total();
            if ($o->menungguJemput()) {
                $jumlahMenunggu++;
            }
        }

        $this->view('transaksi/index', [
            'judulHalaman'    => 'Transaksi',
            'subJudul'        => 'Catat pelanggan kios dan pantau semua transaksi. Klik No. Nota untuk melihat atau mencetak nota.',
            'jsTambahan'      => ['estimasi.js', 'transaksi.js'],
            'error'           => $error,
            'form'            => $form,
            'modeEdit'        => $form['id'] !== '',
            'editMenunggu'    => $diedit && $diedit->menungguJemput(),
            'diskonTetap'     => $diedit ? $diedit->diskon() : 0,
            'namaPromoTetap'  => $diedit ? $diedit->nama_promo : '',
            'layananList'     => $this->layanan->semua(),
            'promoAktif'      => $this->promo->aktif(),
            'statusProses'    => Pesanan::STATUS_PROSES,
            'semua'           => $semua,
            'cari'            => $cari,
            'totalPendapatan' => $totalPendapatan,
            'jumlahMenunggu'  => $jumlahMenunggu,
        ]);
    }
}
