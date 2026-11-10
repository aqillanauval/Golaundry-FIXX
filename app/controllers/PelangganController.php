<?php
// ============================================================
// PelangganController.php  -  CRUD akun Pelanggan (khusus admin)
// Admin bisa menambah, mengubah nama/password, dan menghapus akun pelanggan.
// ============================================================

class PelangganController extends Controller
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function invoke(): void
    {
        $this->wajibLogin('admin');

        $error = '';
        $form = ['id' => '', 'username' => '', 'nama' => ''];

        if ($this->isPost() && $this->aksi() === 'simpan') {
            $form = [
                'id'       => $this->post('id'),
                'username' => normalUser($this->post('username')),
                'nama'     => bersihkan($this->post('nama'), 100),
            ];
            $password = $_POST['password'] ?? '';

            if ($form['id'] !== '') {
                // UPDATE: ubah nama (dan password kalau diisi)
                $p = $this->users->cariById((int)$form['id']);
                if (!$p || $p->isAdmin()) {
                    $error = 'Pelanggan tidak ditemukan.';
                } elseif ($form['nama'] === '') {
                    $error = 'Nama wajib diisi ya.';
                } elseif ($password !== '' && strlen($password) < 6) {
                    $error = 'Password baru minimal 6 karakter.';
                } else {
                    $this->users->ubahNama($p->getId(), $form['nama']);
                    if ($password !== '') {
                        $this->users->ubahPassword($p->getId(), $password);
                    }
                    Logger::catat('Ubah pelanggan', $p->getUsername());
                    $this->flash('sukses', 'Data pelanggan berhasil diubah.');
                    $this->redirect('pelanggan.php');
                }
                $form['username'] = $p ? $p->getUsername() : '';
            } else {
                // CREATE
                if ($form['nama'] === '' || $form['username'] === '') {
                    $error = 'Nama dan email/No. HP wajib diisi ya.';
                } elseif (!usernameValid($form['username'])) {
                    $error = 'Isi dengan email yang valid atau nomor HP (9-15 angka).';
                } elseif (strlen($password) < 6) {
                    $error = 'Password minimal 6 karakter.';
                } elseif ($this->users->tambah($form['username'], $password, $form['nama']) === 0) {
                    $error = 'Email/No. HP ini sudah terdaftar.';
                } else {
                    Logger::catat('Tambah pelanggan', $form['username']);
                    $this->flash('sukses', 'Pelanggan baru berhasil ditambahkan.');
                    $this->redirect('pelanggan.php');
                }
            }
        }

        if ($this->isPost() && $this->aksi() === 'hapus') {
            $p = $this->users->cariById((int)$this->post('id'));
            if ($p && !$p->isAdmin() && $this->users->hapusAkunPelanggan($p->getId())) {
                Logger::catat('Hapus pelanggan', $p->getUsername());
                $this->flash('sukses', 'Akun pelanggan berhasil dihapus. Riwayat transaksinya tetap tersimpan.');
            } else {
                $this->flash('error', 'Akun pelanggan gagal dihapus.');
            }
            $this->redirect('pelanggan.php');
        }

        if ($this->get('edit') !== '' && $error === '') {
            $p = $this->users->cariById((int)$this->get('edit'));
            if ($p && !$p->isAdmin()) {
                $form = ['id' => $p->getId(), 'username' => $p->getUsername(), 'nama' => $p->getNama()];
            }
        }

        $this->view('pelanggan/index', [
            'judulHalaman' => 'Manajemen Pelanggan',
            'subJudul'     => 'Kelola akun pelanggan yang terdaftar di GOLaundry.',
            'error'        => $error,
            'form'         => $form,
            'modeEdit'     => (string)$form['id'] !== '',
            'daftar'       => $this->users->daftarPelanggan(),
        ]);
    }
}
