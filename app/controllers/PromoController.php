<?php
// ============================================================
// PromoController.php  -  CRUD Promo + Upload Foto (khusus admin)
// Data promo di database; file foto di uploads/promo/ (move_uploaded_file).
// ============================================================

class PromoController extends Controller
{
    private PromoModel $model;

    public function __construct()
    {
        $this->model = new PromoModel();
    }

    public function invoke(): void
    {
        $this->wajibLogin('admin');

        $error = '';
        $form = ['id' => '', 'judul' => '', 'deskripsi' => '', 'diskon' => '', 'berlaku_sampai' => '', 'foto' => ''];

        if ($this->isPost() && $this->aksi() === 'simpan') {
            $form = [
                'id'             => $this->post('id'),
                'judul'          => bersihkan($this->post('judul'), 100),
                'deskripsi'      => bersihkan($this->post('deskripsi'), 500),
                'diskon'         => (int)$this->post('diskon'),
                'berlaku_sampai' => $this->post('sampai'),
                'foto'           => '',
            ];
            $lama = $form['id'] !== '' ? $this->model->cari((int)$form['id']) : null;
            $form['foto'] = $lama['foto'] ?? '';
            $tanggalValid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $form['berlaku_sampai']) && strtotime($form['berlaku_sampai']) !== false;

            if ($form['judul'] === '' || $form['deskripsi'] === '') {
                $error = 'Judul dan deskripsi promo wajib diisi.';
            } elseif ($form['diskon'] < 1 || $form['diskon'] > 100) {
                $error = 'Diskon harus antara 1 sampai 100 persen.';
            } elseif (!$tanggalValid) {
                $error = 'Tanggal berlaku sampai wajib diisi.';
            } elseif ($form['id'] !== '' && !$lama) {
                $error = 'Promo tidak ditemukan.';
            } else {
                $fotoBaru = '';
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $hasil = PromoModel::uploadFoto($_FILES['foto']);
                    if (isset($hasil['error'])) {
                        $error = $hasil['error'];
                    } else {
                        $fotoBaru = $hasil['nama'];
                    }
                }

                if ($error === '') {
                    if ($form['id'] === '') {
                        $this->model->tambah($form['judul'], $form['deskripsi'], $form['diskon'], $form['berlaku_sampai'], $fotoBaru);
                        Logger::catat('Tambah promo', $form['judul']);
                        $this->flash('sukses', 'Promo baru berhasil ditambahkan.');
                    } else {
                        $this->model->ubah((int)$form['id'], $form['judul'], $form['deskripsi'], $form['diskon'], $form['berlaku_sampai'], $fotoBaru);
                        Logger::catat('Ubah promo', $form['judul']);
                        $this->flash('sukses', 'Promo berhasil diubah.');
                    }
                    $this->redirect('promo.php');
                }
            }
        }

        if ($this->isPost() && $this->aksi() === 'hapus') {
            $p = $this->model->cari((int)$this->post('id'));
            if ($p && $this->model->hapus((int)$p['id'])) {
                Logger::catat('Hapus promo', $p['judul']);
                $this->flash('sukses', 'Promo berhasil dihapus.');
            }
            $this->redirect('promo.php');
        }

        if ($this->get('edit') !== '' && $error === '') {
            $dipilih = $this->model->cari((int)$this->get('edit'));
            if ($dipilih) {
                $form = $dipilih;
            }
        }

        $this->view('promo/index', [
            'judulHalaman' => 'Manajemen Promo',
            'subJudul'     => 'Buat promo menarik. Pelanggan bisa langsung memakainya saat memesan.',
            'jsTambahan'   => ['promo.js'],
            'error'        => $error,
            'form'         => $form,
            'modeEdit'     => (string)$form['id'] !== '',
            'daftar'       => $this->model->semua(),
        ]);
    }
}
