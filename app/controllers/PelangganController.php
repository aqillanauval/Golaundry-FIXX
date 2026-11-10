<?php
class PelangganController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function invoke()
    {
        $this->harusLogin('admin');

        $error = '';
        $form = ['id' => '', 'username' => '', 'nama' => ''];

        if ($this->adaPost() && $this->post('aksi') == 'simpan') {
            $form['id'] = $this->post('id');
            $form['username'] = normalUser($this->post('username'));
            $form['nama'] = bersihkan($this->post('nama'), 100);
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            if ($form['id'] != '') {                $pelanggan = $this->userModel->cariById($form['id']);
                if (!$pelanggan || $pelanggan->isAdmin()) {
                    $error = 'Pelanggan nggak ketemu.';
                } elseif ($form['nama'] == '') {
                    $error = 'Nama wajib diisi ya.';
                } elseif ($password != '' && strlen($password) < 6) {
                    $error = 'Password baru minimal 6 karakter.';
                } else {
                    $this->userModel->ubahNama($pelanggan->getId(), $form['nama']);
                    if ($password != '') {
                        $this->userModel->ubahPassword($pelanggan->getId(), $password);
                    }
                    catatLog('Ubah pelanggan', $pelanggan->getUsername());
                    $this->pesan('sukses', 'Data pelanggan berhasil diubah.');
                    $this->pindahKe('pelanggan.php');
                }
                if ($pelanggan) {
                    $form['username'] = $pelanggan->getUsername();
                }
            } else {
                if ($form['nama'] == '' || $form['username'] == '') {
                    $error = 'Nama sama email/No. HP wajib diisi ya.';
                } elseif (!usernameValid($form['username'])) {
                    $error = 'Isi pakai email yang valid atau nomor HP (9-15 angka).';
                } elseif (strlen($password) < 6) {
                    $error = 'Password minimal 6 karakter.';
                } elseif ($this->userModel->tambah($form['username'], $password, $form['nama']) == 0) {
                    $error = 'Email/No. HP ini udah terdaftar.';
                } else {
                    catatLog('Tambah pelanggan', $form['username']);
                    $this->pesan('sukses', 'Pelanggan baru berhasil ditambahin.');
                    $this->pindahKe('pelanggan.php');
                }
            }
        }

        if ($this->adaPost() && $this->post('aksi') == 'hapus') {
            $pelanggan = $this->userModel->cariById($this->post('id'));
            if ($pelanggan && !$pelanggan->isAdmin() && $this->userModel->hapusAkunPelanggan($pelanggan->getId())) {
                catatLog('Hapus pelanggan', $pelanggan->getUsername());
                $this->pesan('sukses', 'Akun pelanggan dihapus. Riwayat transaksinya tetap disimpan.');
            } else {
                $this->pesan('error', 'Akun pelanggan gagal dihapus.');
            }
            $this->pindahKe('pelanggan.php');
        }

        if ($this->get('edit') != '' && $error == '') {
            $pelanggan = $this->userModel->cariById($this->get('edit'));
            if ($pelanggan && !$pelanggan->isAdmin()) {
                $form = ['id' => $pelanggan->getId(), 'username' => $pelanggan->getUsername(), 'nama' => $pelanggan->getNama()];
            }
        }

        $this->tampilkan('pelanggan/index', [
            'judulHalaman' => 'Pelanggan',
            'subJudul'     => 'Akun pelanggan yang terdaftar di GOLaundry.',
            'error'        => $error,
            'form'         => $form,
            'modeEdit'     => $form['id'] != '',
            'daftar'       => $this->userModel->semuaPelanggan(),
        ]);
    }
}
