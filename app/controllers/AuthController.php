<?php
class AuthController extends Controller
{
    private $halaman;
    private $userModel;

    public function __construct($halaman)
    {
        $this->halaman = $halaman;
        $this->userModel = new UserModel();
    }

    public function invoke()
    {
        if ($this->halaman == 'index') {
            $this->index();
        } elseif ($this->halaman == 'register') {
            $this->register();
        } elseif ($this->halaman == 'logout') {
            $this->logout();
        } else {
            $this->login();
        }
    }

    private function index()
    {
        $_SESSION = [];
        session_destroy();
        $this->pindahKe('login.php');
    }

    private function login()
    {
        if (isset($_SESSION['user_id'])) {
            $p = $this->userModel->cariById($_SESSION['user_id']);
            if ($p) {
                $this->pindahKe($p->getHalamanDashboard());
            }
        }

        $error = '';
        $usernameIsi = isset($_COOKIE['ingat_user']) ? $_COOKIE['ingat_user'] : '';
        $ingatDicentang = isset($_COOKIE['ingat_user']);

        if ($this->adaPost()) {
            $this->cekToken();
            $username = normalUser($this->post('username'));
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $ingat = isset($_POST['ingat']);
            $usernameIsi = $username;
            $ingatDicentang = $ingat;

            if ($username == '' || $password == '') {
                $error = 'Email/No. HP sama password wajib diisi ya.';
            } else {
                $pengguna = $this->userModel->cariByUsername($username);

                if ($pengguna && $pengguna->cekPassword($password)) {
                    session_regenerate_id(true); 
                    $_SESSION['user_id'] = $pengguna->getId();
                    $_SESSION['user'] = $pengguna->getUsername();
                    $_SESSION['nama'] = $pengguna->getNama();
                    $_SESSION['role'] = $pengguna->getRole();
                    unset($_SESSION['token']); 
                    if ($ingat) {
                        setcookie('ingat_user', $username, time() + (60 * 60 * 24 * 30), '/');
                    } else {
                        setcookie('ingat_user', '', time() - 3600, '/'); // hapus cookie
                    }

                    catatLog('Login berhasil');
                    $this->pindahKe($pengguna->getHalamanDashboard());
                }

                catatLog('Login gagal', $username, 'tamu');
                $error = 'Email/No. HP atau password salah.';
            }
        }

        $this->tampilkan('auth/login', [
            'error'          => $error,
            'usernameIsi'    => $usernameIsi,
            'ingatDicentang' => $ingatDicentang,
        ]);
    }

    private function register()
    {
        $error = '';
        $isi = ['nama' => '', 'username' => ''];

        if ($this->adaPost()) {
            $this->cekToken();
            $nama = bersihkan($this->post('nama'), 100);
            $username = normalUser($this->post('username'));
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $ulangi = isset($_POST['ulangi']) ? $_POST['ulangi'] : '';
            $isi = ['nama' => $nama, 'username' => $username];

            if ($nama == '' || $username == '' || $password == '' || $ulangi == '') {
                $error = 'Semua kolom wajib diisi ya.';
            } elseif (!usernameValid($username)) {
                $error = 'Isi pakai email yang valid atau nomor HP (9-15 angka).';
            } elseif (strlen($password) < 6) {
                $error = 'Password minimal 6 karakter.';
            } elseif ($password != $ulangi) {
                $error = 'Password yang kamu ulangi belum sama.';
            } elseif ($this->userModel->tambah($username, $password, $nama) == 0) {
                $error = 'Email/No. HP ini udah terdaftar.';
            } else {
                catatLog('Daftar akun baru', $nama, $username);
                $this->pesan('sukses', 'Akun berhasil dibuat! Yuk login.');
                $this->pindahKe('login.php');
            }
        }

        $this->tampilkan('auth/register', ['error' => $error, 'isi' => $isi]);
    }

    private function logout()
    {
        if (isset($_SESSION['user'])) {
            catatLog('Logout');
        }
        $_SESSION = [];
        session_destroy();

        session_start(); 
        $this->pesan('sukses', 'Kamu udah keluar. Sampai jumpa lagi!');
        $this->pindahKe('login.php');
    }
}
