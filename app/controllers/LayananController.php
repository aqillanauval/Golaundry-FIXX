<?php
// ============================================================
// LayananController.php  -  CRUD Layanan (khusus admin)
//   Create : form "Tambah Layanan"        Read   : tabel daftar layanan
//   Update : ?edit=ID -> form terisi       Delete : tombol Hapus
// ============================================================

class LayananController extends Controller
{
    private LayananModel $model;

    public function __construct()
    {
        $this->model = new LayananModel();
    }

    public function invoke(): void
    {
        $this->wajibLogin('admin');

        $error = '';
        $form = ['id' => '', 'nama' => '', 'harga' => '', 'satuan' => 'kg'];

        if ($this->isPost() && $this->aksi() === 'simpan') {
            $form = [
                'id'     => $this->post('id'),
                'nama'   => bersihkan($this->post('nama'), 100),
                'harga'  => (int)$this->post('harga'),
                'satuan' => bersihkan($this->post('satuan'), 20),
            ];

            if ($form['nama'] === '' || $form['harga'] <= 0 || $form['satuan'] === '') {
                $error = 'Nama, harga, dan satuan wajib diisi dengan benar ya.';
            } elseif ($form['id'] === '') {
                $this->model->tambah($form['nama'], $form['harga'], $form['satuan']);
                Logger::catat('Tambah layanan', $form['nama']);
                $this->flash('sukses', 'Layanan baru berhasil ditambahkan.');
                $this->redirect('layanan.php');
            } else {
                $this->model->ubah((int)$form['id'], $form['nama'], $form['harga'], $form['satuan']);
                Logger::catat('Ubah layanan', $form['nama']);
                $this->flash('sukses', 'Layanan berhasil diubah.');
                $this->redirect('layanan.php');
            }
        }

        if ($this->isPost() && $this->aksi() === 'hapus') {
            $layanan = $this->model->cari((int)$this->post('id'));
            if ($layanan && $this->model->hapus((int)$layanan['id'])) {
                Logger::catat('Hapus layanan', $layanan['nama']);
                $this->flash('sukses', 'Layanan berhasil dihapus. Pesanan lama tetap menyimpan nama & harganya.');
            }
            $this->redirect('layanan.php');
        }

        if ($this->get('edit') !== '' && $error === '') {
            $dipilih = $this->model->cari((int)$this->get('edit'));
            if ($dipilih) {
                $form = $dipilih;
            }
        }

        $this->view('layanan/index', [
            'judulHalaman' => 'Manajemen Layanan',
            'subJudul'     => 'Atur daftar layanan dan harganya. Ini yang muncul di form pesanan pelanggan.',
            'error'        => $error,
            'form'         => $form,
            'modeEdit'     => (string)$form['id'] !== '',
            'daftar'       => $this->model->semua(),
        ]);
    }
}
