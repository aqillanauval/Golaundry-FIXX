<?php
// KaryawanController.php  -  CRUD Karyawan (khusus admin)

class KaryawanController extends Controller
{
    private KaryawanModel $model;

    public function __construct()
    {
        $this->model = new KaryawanModel();
    }

    public function invoke(): void
    {
        $this->wajibLogin('admin');

        $error = '';
        $form = ['id' => '', 'nama' => '', 'jabatan' => '', 'no_hp' => ''];

        if ($this->isPost() && $this->aksi() === 'simpan') {
            $form = [
                'id'      => $this->post('id'),
                'nama'    => bersihkan($this->post('nama'), 100),
                'jabatan' => bersihkan($this->post('jabatan'), 50),
                'no_hp'   => bersihkan($this->post('no_hp'), 20),
            ];

            if ($form['nama'] === '' || $form['jabatan'] === '' || $form['no_hp'] === '') {
                $error = 'Nama, jabatan, dan No. HP wajib diisi ya.';
            } elseif (!preg_match('/^\+?[0-9]{9,15}$/', $form['no_hp'])) {
                $error = 'No. HP harus berupa angka (9-15 digit).';
            } else {
                if ($form['id'] === '') {
                    $this->model->tambah($form['nama'], $form['jabatan'], $form['no_hp']);
                    Logger::catat('Tambah karyawan', $form['nama']);
                    $this->flash('sukses', 'Karyawan baru berhasil ditambahkan.');
                } else {
                    $this->model->ubah((int)$form['id'], $form['nama'], $form['jabatan'], $form['no_hp']);
                    Logger::catat('Ubah karyawan', $form['nama']);
                    $this->flash('sukses', 'Data karyawan berhasil diubah.');
                }
                $this->redirect('karyawan.php');
            }
        }

        if ($this->isPost() && $this->aksi() === 'hapus') {
            $k = $this->model->cari((int)$this->post('id'));
            if ($k && $this->model->hapus((int)$k['id'])) {
                Logger::catat('Hapus karyawan', $k['nama']);
                $this->flash('sukses', 'Karyawan berhasil dihapus.');
            }
            $this->redirect('karyawan.php');
        }

        if ($this->get('edit') !== '' && $error === '') {
            $dipilih = $this->model->cari((int)$this->get('edit'));
            if ($dipilih) {
                $form = $dipilih;
            }
        }

        $this->view('karyawan/index', [
            'judulHalaman' => 'Manajemen Karyawan',
            'subJudul'     => 'Kelola data karyawan yang bekerja di GOLaundry.',
            'error'        => $error,
            'form'         => $form,
            'modeEdit'     => (string)$form['id'] !== '',
            'daftar'       => $this->model->semua(),
        ]);
    }
}
