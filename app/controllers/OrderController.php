<?php
// ============================================================
// OrderController.php  -  CRUD Pesanan milik pelanggan (+ pakai promo)
//
// ALUR: pelanggan pilih layanan + alamat (berat belum diketahui = 0)
//       -> status "Menunggu Jemput" -> kurir menimbang -> "Diterima"
// Edit & batal hanya boleh selama status masih "Menunggu Jemput".
// Besar diskon SELALU diambil dari tabel promo di server, bukan dari browser.
// ============================================================

class OrderController extends Controller
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
        $saya = $this->wajibLogin('customer');
        $error = '';
        $form = ['id' => '', 'layanan' => '', 'catatan' => '', 'promo' => ''];

        // ----- CREATE & UPDATE -----
        if ($this->isPost() && $this->aksi() === 'simpan') {
            $form = [
                'id'      => $this->post('id'),
                'layanan' => $this->post('layanan'),
                'catatan' => bersihkan($this->post('catatan'), 500),
                'promo'   => $this->post('promo'),
            ];
            $layananDipilih = $form['layanan'] !== '' ? $this->layanan->cari((int)$form['layanan']) : null;

            // tentukan promo: '' = tanpa promo, 'lama' = promo pesanan ini tetap, angka = id promo
            $promo = null;
            $promoError = '';
            if ($form['promo'] === 'lama') {
                $promo = 'lama';
                if ($form['id'] === '') {
                    $promoError = 'Promo tidak valid.';
                }
            } elseif ($form['promo'] !== '') {
                $promo = $this->promo->cariAktif((int)$form['promo']);
                if (!$promo) {
                    $promoError = 'Promo tidak valid atau sudah berakhir.';
                }
            }

            if (!$layananDipilih) {
                $error = 'Silakan pilih layanan dulu ya.';
            } elseif ($form['catatan'] === '') {
                $error = 'Isi alamat jemput dulu ya, supaya kurir bisa menemukanmu.';
            } elseif ($promoError !== '') {
                $error = $promoError;
            } elseif ($form['id'] === '') {
                $kode = $this->pesanan->buat([
                    'user_id'        => $saya->getId(),
                    'nama_pelanggan' => $saya->getNama(),
                    'sumber'         => 'aplikasi',
                    'layanan'        => $layananDipilih,
                    'berat'          => 0,
                    'catatan'        => $form['catatan'],
                    'status'         => 'Menunggu Jemput',
                    'promo'          => $promo,
                ]);
                Logger::catat('Buat pesanan', $kode . ' ' . $layananDipilih['nama']);
                $this->flash('sukses', 'Pesanan berhasil dibuat. Kurir akan menjemput cucianmu dan menimbangnya.');
                $this->redirect('dashboard-customer.php');
            } elseif ($this->pesanan->ubahOlehPelanggan($form['id'], $saya->getId(), $layananDipilih, $form['catatan'], $promo)) {
                Logger::catat('Ubah pesanan', $form['id']);
                $this->flash('sukses', 'Pesanan berhasil diubah.');
                $this->redirect('order.php');
            } else {
                $error = 'Pesanan tidak ditemukan atau sudah dijemput.';
            }
        }

        // ----- DELETE (batalkan) -----
        if ($this->isPost() && $this->aksi() === 'hapus') {
            $kode = $this->post('id');
            if ($this->pesanan->batalkan($kode, $saya->getId())) {
                Logger::catat('Batalkan pesanan', $kode);
                $this->flash('sukses', 'Pesanan berhasil dibatalkan.');
            } else {
                $this->flash('error', 'Pesanan tidak bisa dibatalkan karena cucianmu sudah dijemput.');
            }
            $this->redirect('order.php');
        }

        $promoAktif = $this->promo->aktif();
        $promoLama = null; // promo dari pesanan yang diedit, kalau sudah tidak ada di daftar aktif

        // ----- mode edit: ?edit=KODE -----
        if ($this->get('edit') !== '' && $error === '') {
            $o = $this->pesanan->cariMilikUser($this->get('edit'), $saya->getId());
            if ($o && $o->menungguJemput()) {
                $form = ['id' => $o->kode, 'layanan' => (string)$o->layanan_id, 'catatan' => $o->catatan, 'promo' => ''];
                if ($o->diskon() > 0) {
                    $form['promo'] = 'lama';
                    foreach ($promoAktif as $p) {
                        if ((int)$p['id'] === (int)$o->promo_id && (int)$p['diskon'] === $o->diskon()) {
                            $form['promo'] = (string)$p['id']; // promonya masih aktif
                        }
                    }
                }
            }
        }

        // tombol "Pakai promo ini" di dashboard -> ?promo=ID
        if ($form['id'] === '' && $form['promo'] === '' && $this->get('promo') !== '') {
            if ($this->promo->cariAktif((int)$this->get('promo'))) {
                $form['promo'] = $this->get('promo');
            }
        }

        // pilihan "promo dari pesanan ini" (juga saat gagal simpan di mode edit)
        if ($form['promo'] === 'lama' && $form['id'] !== '') {
            $ol = $this->pesanan->cariMilikUser($form['id'], $saya->getId());
            if ($ol && $ol->diskon() > 0) {
                $promoLama = ['judul' => $ol->nama_promo, 'diskon' => $ol->diskon()];
            }
        }

        $modeEdit = ($form['id'] !== '');
        $this->view('order/index', [
            'judulHalaman' => $modeEdit ? 'Edit pesanan' : 'Buat pesanan',
            'subJudul'     => 'Pilih layanannya saja. Berat cucian ditimbang kurir saat menjemput.',
            'jsTambahan'   => ['order.js'],
            'error'        => $error,
            'form'         => $form,
            'modeEdit'     => $modeEdit,
            'layananList'  => $this->layanan->semua(),
            'promoAktif'   => $promoAktif,
            'promoLama'    => $promoLama,
            'pesananSaya'  => $this->pesanan->milikUser($saya->getId()),
        ]);
    }
}
