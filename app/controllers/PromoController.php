<?php
// ============================================================
// PromoController.php  ->  CRUD Promo + upload foto (khusus admin)
// ============================================================

class PromoController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new PromoModel();
    }

    public function invoke()
    {
        $this->harusLogin('admin');

        $error = '';
        $form = ['id' => '', 'judul' => '', 'deskripsi' => '', 'diskon' => '', 'berlaku_sampai' => '', 'foto' => ''];

        // ----- CREATE & UPDATE -----
        if ($this->adaPost() && $this->post('aksi') == 'simpan') {
            $form['id'] = $this->post('id');
            $form['judul'] = bersihkan($this->post('judul'), 100);
            $form['deskripsi'] = bersihkan($this->post('deskripsi'), 500);
            $form['diskon'] = (int)$this->post('diskon');
            $form['berlaku_sampai'] = $this->post('sampai');

            $lama = null;
            if ($form['id'] != '') {
                $lama = $this->model->cari($form['id']);
                if ($lama) {
                    $form['foto'] = $lama['foto'];
                }
            }
            $tanggalValid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $form['berlaku_sampai']);

            if ($form['judul'] == '' || $form['deskripsi'] == '') {
                $error = 'Judul sama deskripsi promo wajib diisi.';
            } elseif ($form['diskon'] < 1 || $form['diskon'] > 100) {
                $error = 'Diskon harus antara 1 sampai 100 persen.';
            } elseif (!$tanggalValid) {
                $error = 'Tanggal "berlaku sampai" wajib diisi.';
            } elseif ($form['id'] != '' && !$lama) {
                $error = 'Promo nggak ketemu.';
            } else {
                // kalau admin milih file foto, upload dulu
                $fotoBaru = '';
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] != UPLOAD_ERR_NO_FILE) {
                    $hasil = $this->model->uploadFoto($_FILES['foto']);
                    if (isset($hasil['error'])) {
                        $error = $hasil['error'];
                    } else {
                        $fotoBaru = $hasil['nama'];
                    }
                }

                if ($error == '') {
                    if ($form['id'] == '') {
                        $this->model->tambah($form['judul'], $form['deskripsi'], $form['diskon'], $form['berlaku_sampai'], $fotoBaru);
                        catatLog('Tambah promo', $form['judul']);
                        $this->pesan('sukses', 'Promo baru berhasil ditambahin.');
                    } else {
                        $this->model->ubah($form['id'], $form['judul'], $form['deskripsi'], $form['diskon'], $form['berlaku_sampai'], $fotoBaru);
                        catatLog('Ubah promo', $form['judul']);
                        $this->pesan('sukses', 'Promo berhasil diubah.');
                    }
                    $this->pindahKe('promo.php');
                }
            }
        }

        // ----- DELETE -----
        if ($this->adaPost() && $this->post('aksi') == 'hapus') {
            $promo = $this->model->cari($this->post('id'));
            if ($promo && $this->model->hapus($promo['id'])) {
                catatLog('Hapus promo', $promo['judul']);
                $this->pesan('sukses', 'Promo berhasil dihapus.');
            }
            $this->pindahKe('promo.php');
        }

        if ($this->get('edit') != '' && $error == '') {
            $dipilih = $this->model->cari($this->get('edit'));
            if ($dipilih) {
                $form = $dipilih;
            }
        }

        $this->tampilkan('promo/index', [
            'judulHalaman' => 'Promo',
            'subJudul'     => 'Bikin promo, pelanggan bisa langsung pakai waktu pesan.',
            'jsTambahan'   => ['promo.js'],
            'error'        => $error,
            'form'         => $form,
            'modeEdit'     => $form['id'] != '',
            'daftar'       => $this->model->semua(),
        ]);
    }
}
