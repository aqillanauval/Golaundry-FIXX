<?php
class OrderController extends Controller
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
        $saya = $this->harusLogin('customer');
        $error = '';
        $form = ['id' => '', 'layanan' => '', 'catatan' => '', 'promo' => ''];

        if ($this->adaPost() && $this->post('aksi') == 'simpan') {
            $form['id'] = $this->post('id');           
            $form['layanan'] = $this->post('layanan');  
            $form['catatan'] = bersihkan($this->post('catatan'), 500);
            $form['promo'] = $this->post('promo');      // '' / 'lama' / id promo

            $layanan = null;
            if ($form['layanan'] != '') {
                $layanan = $this->layananModel->cari($form['layanan']);
            }

            $promo = null;
            $errorPromo = '';
            if ($form['promo'] == 'lama') {
                $promo = 'lama'; 
                if ($form['id'] == '') {
                    $errorPromo = 'Promo nggak valid.';
                }
            } elseif ($form['promo'] != '') {
                $promo = $this->promoModel->cariAktif($form['promo']);
                if (!$promo) {
                    $errorPromo = 'Promo nggak valid atau udah habis masa berlakunya.';
                }
            }

            if (!$layanan) {
                $error = 'Pilih layanannya dulu ya.';
            } elseif ($form['catatan'] == '') {
                $error = 'Isi alamat jemput dulu ya, biar kurir bisa nemuin kamu.';
            } elseif ($errorPromo != '') {
                $error = $errorPromo;
            } elseif ($form['id'] == '') {
                // CREATE
                $kode = $this->pesananModel->buat([
                    'user_id'        => $saya->getId(),
                    'nama_pelanggan' => $saya->getNama(),
                    'sumber'         => 'aplikasi',
                    'layanan'        => $layanan,
                    'berat'          => 0,
                    'catatan'        => $form['catatan'],
                    'status'         => 'Menunggu Jemput',
                    'promo'          => $promo,
                ]);
                catatLog('Buat pesanan', $kode . ' ' . $layanan['nama']);
                $this->pesan('sukses', 'Pesanan masuk! Kurir bakal jemput & nimbang cucianmu.');
                $this->pindahKe('dashboard-customer.php');
            } elseif ($this->pesananModel->ubahOlehPelanggan($form['id'], $saya->getId(), $layanan, $form['catatan'], $promo)) {
                // UPDATE
                catatLog('Ubah pesanan', $form['id']);
                $this->pesan('sukses', 'Pesanan berhasil diubah.');
                $this->pindahKe('order.php');
            } else {
                $error = 'Pesanan nggak ketemu atau udah keburu dijemput.';
            }
        }

        if ($this->adaPost() && $this->post('aksi') == 'hapus') {
            $kode = $this->post('id');
            if ($this->pesananModel->batalkan($kode, $saya->getId())) {
                catatLog('Batalkan pesanan', $kode);
                $this->pesan('sukses', 'Pesanan berhasil dibatalin.');
            } else {
                $this->pesan('error', 'Pesanan nggak bisa dibatalin karena cucianmu udah dijemput.');
            }
            $this->pindahKe('order.php');
        }

        $promoAktif = $this->promoModel->aktif();

        if ($this->get('edit') != '' && $error == '') {
            $p = $this->pesananModel->cariMilikUser($this->get('edit'), $saya->getId());
            if ($p && $p->menungguJemput()) {
                $form = ['id' => $p->kode, 'layanan' => (string)$p->layanan_id, 'catatan' => $p->catatan, 'promo' => ''];

                if ($p->punyaDiskon()) {
                    $form['promo'] = 'lama';
                    foreach ($promoAktif as $promo) {
                        if ($promo['id'] == $p->promo_id && $promo['diskon'] == $p->diskon) {
                            $form['promo'] = (string)$promo['id'];
                        }
                    }
                }
            }
        }

        if ($form['id'] == '' && $form['promo'] == '' && $this->get('promo') != '') {
            if ($this->promoModel->cariAktif($this->get('promo'))) {
                $form['promo'] = $this->get('promo');
            }
        }

        $promoLama = null;
        if ($form['promo'] == 'lama' && $form['id'] != '') {
            $p = $this->pesananModel->cariMilikUser($form['id'], $saya->getId());
            if ($p && $p->punyaDiskon()) {
                $promoLama = ['judul' => $p->nama_promo, 'diskon' => $p->diskon];
            }
        }

        $modeEdit = $form['id'] != '';
        $this->tampilkan('order/index', [
            'judulHalaman' => $modeEdit ? 'Edit pesanan' : 'Buat pesanan',
            'subJudul'     => 'Pilih layanannya aja. Berat cucian ditimbang kurir pas jemput.',
            'jsTambahan'   => ['order.js'],
            'error'        => $error,
            'form'         => $form,
            'modeEdit'     => $modeEdit,
            'layananList'  => $this->layananModel->semua(),
            'promoAktif'   => $promoAktif,
            'promoLama'    => $promoLama,
            'pesananSaya'  => $this->pesananModel->milikUser($saya->getId()),
        ]);
    }
}
