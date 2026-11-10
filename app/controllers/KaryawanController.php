<?php
// KaryawanController.php  ->  halaman CRUD Karyawan (khusus admin)

class KaryawanController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new KaryawanModel();
    }

    public function invoke()
    {
        $this->harusLogin('admin');

        $error = '';
        $form = ['id' => '', 'nama' => '', 'jabatan' => '', 'no_hp' => ''];

        // ----- CREATE & UPDATE -----
        if ($this->adaPost() && $this->post('aksi') == 'simpan') {
            $form['id'] = $this->post('id');
            $form['nama'] = bersihkan($this->post('nama'), 100);
            $form['jabatan'] = bersihkan($this->post('jabatan'), 50);
            $form['no_hp'] = bersihkan($this->post('no_hp'), 20);

            if ($form['nama'] == '' || $form['jabatan'] == '' || $form['no_hp'] == '') {
                $error = 'Nama, jabatan, sama No. HP wajib diisi ya.';
            } elseif (!preg_match('/^\+?[0-9]{9,15}$/', $form['no_hp'])) {
                $error = 'No. HP harus angka (9-15 digit).';
            } else {
                if ($form['id'] == '') {
                    $this->model->tambah($form['nama'], $form['jabatan'], $form['no_hp']);
                    catatLog('Tambah karyawan', $form['nama']);
                    $this->pesan('sukses', 'Karyawan baru berhasil ditambahin.');
                } else {
                    $this->model->ubah($form['id'], $form['nama'], $form['jabatan'], $form['no_hp']);
                    catatLog('Ubah karyawan', $form['nama']);
                    $this->pesan('sukses', 'Data karyawan berhasil diubah.');
                }
                $this->pindahKe('karyawan.php');
            }
        }

        // ----- DELETE -----
        if ($this->adaPost() && $this->post('aksi') == 'hapus') {
            $karyawan = $this->model->cari($this->post('id'));
            if ($karyawan) {
                $this->model->hapus($karyawan['id']);
                catatLog('Hapus karyawan', $karyawan['nama']);
                $this->pesan('sukses', 'Karyawan berhasil dihapus.');
            }
            $this->pindahKe('karyawan.php');
        }

        if ($this->get('edit') != '' && $error == '') {
            $dipilih = $this->model->cari($this->get('edit'));
            if ($dipilih) {
                $form = $dipilih;
            }
        }

        $this->tampilkan('karyawan/index', [
            'judulHalaman' => 'Karyawan',
            'subJudul'     => 'Data karyawan yang kerja di GOLaundry.',
            'error'        => $error,
            'form'         => $form,
            'modeEdit'     => $form['id'] != '',
            'daftar'       => $this->model->semua(),
        ]);
    }
}
