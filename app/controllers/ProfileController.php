<?php
class ProfileController extends Controller
{
    public function invoke()
    {
        $saya = $this->harusLogin();
        $userModel = new UserModel();

        $error = '';
        $errorHapus = '';
        $namaDiForm = $saya->getNama();

        if ($this->adaPost() && $this->post('aksi') == 'simpan') {
            $namaBaru = bersihkan($this->post('nama'), 100);
            $passwordBaru = isset($_POST['password_baru']) ? $_POST['password_baru'] : '';
            $namaDiForm = $namaBaru;

            if ($namaBaru == '') {
                $error = 'Nama nggak boleh kosong.';
            } elseif ($passwordBaru != '' && strlen($passwordBaru) < 6) {
                $error = 'Password baru minimal 6 karakter.';
            } else {
                $userModel->ubahNama($saya->getId(), $namaBaru);
                if ($passwordBaru != '') {
                    $userModel->ubahPassword($saya->getId(), $passwordBaru);
                }
                $_SESSION['nama'] = $namaBaru; // update juga yang di session
                catatLog('Ubah profil', $passwordBaru != '' ? 'nama & password' : 'nama');
                $this->pesan('sukses', 'Profil berhasil disimpan.');
                $this->pindahKe('profile.php');
            }
        }

        if ($this->adaPost() && $this->post('aksi') == 'hapus_akun' && !$saya->isAdmin()) {
            $passwordKonfirmasi = isset($_POST['konfirmasi_password']) ? $_POST['konfirmasi_password'] : '';

            if (!$saya->cekPassword($passwordKonfirmasi)) {
                $errorHapus = 'Password-nya salah, akun nggak jadi dihapus.';
            } elseif ($userModel->hapusAkunPelanggan($saya->getId())) {
                catatLog('Hapus akun sendiri', $saya->getNama());
                $_SESSION = [];
                session_regenerate_id(true);
                $this->pesan('sukses', 'Akun kamu udah dihapus. Makasih udah pakai GOLaundry!');
                $this->pindahKe('login.php');
            } else {
                $errorHapus = 'Akun gagal dihapus, coba lagi ya.';
            }
        }

        $this->tampilkan('profile/index', [
            'judulHalaman' => 'Profil Saya',
            'subJudul'     => 'Atur data diri akunmu.',
            'error'        => $error,
            'errorHapus'   => $errorHapus,
            'namaDiForm'   => $namaDiForm,
        ]);
    }
}
