<?php
// ============================================================
// AuthController.php  -  Login, Registrasi, Logout
//
// STATE yang dipakai:
//   SESSION : status login ($_SESSION['user_id'], 'user', 'nama', 'role')
//   COOKIE  : 'ingat_user' (fitur "Ingat saya", 30 hari)
// Login dicek ke tabel users dengan prepared statement + password_verify().
// ============================================================

class AuthController extends Controller
{
    private string $halaman;
    private UserModel $users;

    // $halaman: 'login' / 'register' / 'logout' / 'index'
    public function __construct(string $halaman)
    {
        $this->halaman = $halaman;
        $this->users = new UserModel();
    }

    public function invoke(): void
    {
        switch ($this->halaman) {
            case 'register':
                $this->register();
                break;
            case 'logout':
                $this->logout();
                break;
            case 'index':
                $this->index();
                break;
            default:
                $this->login();
        }
    }

    // index.php: selalu mulai dari halaman login, sesi lama dihapus
    private function index(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirect('login.php');
    }

    private function login(): void
    {
        // sudah login? langsung ke dashboard
        if (isset($_SESSION['user_id'])) {
            $p = $this->users->cariById((int)$_SESSION['user_id']);
            if ($p) {
                $this->redirect($p->urlDashboard());
            }
        }

        $error = '';
        $usernameIsi = $_COOKIE['ingat_user'] ?? '';
        $ingatCentang = isset($_COOKIE['ingat_user']);

        if ($this->isPost()) {
            $this->cekCsrf();
            $username = normalUser($this->post('username'));
            $password = $_POST['password'] ?? '';
            $ingat = isset($_POST['ingat']);
            $usernameIsi = $username;
            $ingatCentang = $ingat;

            if ($username === '' || $password === '') {
                $error = 'Email/No. HP dan password wajib diisi ya.';
            } else {
                $pengguna = $this->users->cariByUsername($username);

                if ($pengguna && $pengguna->cekPassword($password)) {
                    session_regenerate_id(true); // ganti ID session biar lebih aman
                    $_SESSION['user_id'] = $pengguna->getId();
                    $_SESSION['user'] = $pengguna->getUsername();
                    $_SESSION['nama'] = $pengguna->getNama();
                    $_SESSION['role'] = $pengguna->getRole();
                    unset($_SESSION['csrf']); // token baru setelah login

                    if ($ingat) {
                        setcookie('ingat_user', $username, ['expires' => time() + 60 * 60 * 24 * 30, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
                    } else {
                        setcookie('ingat_user', '', ['expires' => time() - 3600, 'path' => '/']);
                    }

                    Logger::catat('Login berhasil');
                    $this->redirect($pengguna->urlDashboard());
                }
                Logger::catat('Login gagal', $username);
                $error = 'Email/No. HP atau password salah.';
            }
        }

        $this->view('auth/login', compact('error', 'usernameIsi', 'ingatCentang'));
    }

    private function register(): void
    {
        $error = '';
        $isi = ['nama' => '', 'username' => ''];

        if ($this->isPost()) {
            $this->cekCsrf();
            $nama = bersihkan($this->post('nama'), 100);
            $username = normalUser($this->post('username'));
            $password = $_POST['password'] ?? '';
            $ulangi = $_POST['ulangi'] ?? '';
            $isi = ['nama' => $nama, 'username' => $username];

            if ($nama === '' || $username === '' || $password === '' || $ulangi === '') {
                $error = 'Semua kolom wajib diisi ya.';
            } elseif (!usernameValid($username)) {
                $error = 'Isi dengan email yang valid atau nomor HP (9-15 angka).';
            } elseif (strlen($password) < 6) {
                $error = 'Password minimal 6 karakter.';
            } elseif ($password !== $ulangi) {
                $error = 'Ulangi password belum sama.';
            } elseif ($this->users->tambah($username, $password, $nama) === 0) {
                $error = 'Email/No. HP ini sudah terdaftar.';
            } else {
                Logger::catat('Registrasi akun baru', $nama, $username);
                $this->flash('sukses', 'Akun berhasil dibuat! Silakan masuk.');
                $this->redirect('login.php');
            }
        }

        $this->view('auth/register', compact('error', 'isi'));
    }

    // cookie "ingat saya" sengaja dibiarkan supaya email tetap terisi di form login
    private function logout(): void
    {
        if (isset($_SESSION['user'])) {
            Logger::catat('Logout');
        }
        $_SESSION = [];
        session_destroy();
        session_start();
        $this->flash('sukses', 'Kamu sudah keluar. Sampai jumpa lagi!');
        $this->redirect('login.php');
    }
}
