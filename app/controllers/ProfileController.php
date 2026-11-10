<?php
// ============================================================
// ProfileController.php  -  Ubah Profil & Hapus Akun (admin & pelanggan)
//   aksi=simpan     : ubah nama dan/atau password
//   aksi=hapus_akun : pelanggan menghapus akunnya sendiri (wajib isi password),
//                     dijalankan sebagai TRANSAKSI di UserModel
// ============================================================

class ProfileController extends Controller
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function invoke(): void
    {
        $saya = $this->wajibLogin();
        $error = '';
        $errorHapus = '';
        $namaTampil = $saya->getNama();

        if ($this->isPost() && $this->aksi() === 'simpan') {
            $namaBaru = bersihkan($this->post('nama'), 100);
            $passwordBaru = $_POST['password_baru'] ?? '';
            $namaTampil = $namaBaru;

            if ($namaBaru === '') {
                $error = 'Nama tidak boleh kosong.';
            } elseif ($passwordBaru !== '' && strlen($passwordBaru) < 6) {
                $error = 'Password baru minimal 6 karakter.';
            } else {
                $this->users->ubahNama($saya->getId(), $namaBaru);
                if ($passwordBaru !== '') {
                    $this->users->ubahPassword($saya->getId(), $passwordBaru);
                }
                $saya->setNama($namaBaru);
                $_SESSION['nama'] = $namaBaru;
                Logger::catat('Ubah profil', $passwordBaru !== '' ? 'nama & password' : 'nama');
                $this->flash('sukses', 'Profil berhasil diperbarui.');
                $this->redirect('profile.php');
            }
        }

        if ($this->isPost() && $this->aksi() === 'hapus_akun' && !$saya->isAdmin()) {
            if (!$saya->cekPassword($_POST['konfirmasi_password'] ?? '')) {
                $errorHapus = 'Password salah. Akun tidak dihapus.';
            } elseif ($this->users->hapusAkunPelanggan($saya->getId())) {
                Logger::catat('Hapus akun sendiri', $saya->getNama());
                $_SESSION = [];
                session_regenerate_id(true);
                $this->flash('sukses', 'Akunmu sudah dihapus. Terima kasih sudah memakai GOLaundry.');
                $this->redirect('login.php');
            } else {
                $errorHapus = 'Akun gagal dihapus. Silakan coba lagi.';
            }
        }

        $this->view('profile/index', [
            'judulHalaman' => 'Profil Saya',
            'subJudul'     => 'Kelola data diri akunmu.',
            'error'        => $error,
            'errorHapus'   => $errorHapus,
            'namaTampil'   => $namaTampil,
        ]);
    }
}
