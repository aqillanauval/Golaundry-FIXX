<?php
// ============================================================
// Controller.php  -  Kelas induk semua controller (bagian "C" di MVC).
//
// Alur MVC di proyek ini (sama seperti contoh index.php -> c_programKerja):
//   1. Browser membuka layanan.php
//   2. layanan.php membuat  $controller = new LayananController();  lalu  invoke()
//   3. Controller membaca input, memanggil MODEL (database)
//   4. Controller mengirim data ke VIEW (HTML) lewat $this->view()
// ============================================================

abstract class Controller
{
    protected ?Pengguna $pengguna = null; // pengguna yang sedang login (null = belum login)

    // setiap controller wajib punya invoke(): titik masuk dari halaman
    abstract public function invoke(): void;

    // ---------- View ----------

    // tampilkan file di app/views/. $data dipecah jadi variabel biasa:
    // ['judul' => 'X']  ->  di view bisa dipakai sebagai $judul
    protected function view(string $nama, array $data = []): void
    {
        $data['pengguna'] = $this->pengguna;
        extract($data);
        require APP_PATH . '/views/' . $nama . '.php';
    }

    // balasan JSON untuk AJAX (fetch)
    protected function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // ---------- Request ----------

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function post(string $kunci, string $bawaan = ''): string
    {
        return isset($_POST[$kunci]) && is_string($_POST[$kunci]) ? trim($_POST[$kunci]) : $bawaan;
    }

    protected function get(string $kunci, string $bawaan = ''): string
    {
        return isset($_GET[$kunci]) && is_string($_GET[$kunci]) ? trim($_GET[$kunci]) : $bawaan;
    }

    protected function aksi(): string
    {
        return $this->post('aksi');
    }

    // ---------- Respons ----------

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    // pesan sekali tampil (disimpan di SESSION, dihapus setelah dibaca)
    protected function flash(string $tipe, string $pesan): void
    {
        $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
    }

    // ---------- Keamanan ----------

    // Panggil di awal invoke() halaman yang butuh login.
    // $role: 'admin' / 'customer' kalau halaman khusus role tertentu.
    protected function wajibLogin(?string $role = null): Pengguna
    {
        if (!isset($_SESSION['user_id'])) {
            $this->flash('error', 'Silakan login dulu ya.');
            $this->redirect('login.php');
        }

        // ambil ulang data dari database: kalau akun sudah dihapus, sesi diakhiri
        $this->pengguna = (new UserModel())->cariById((int)$_SESSION['user_id']);
        if ($this->pengguna === null) {
            $_SESSION = [];
            $this->flash('error', 'Akunmu sudah tidak terdaftar.');
            $this->redirect('login.php');
        }
        $_SESSION['nama'] = $this->pengguna->getNama();
        $_SESSION['role'] = $this->pengguna->getRole();

        if ($role !== null && $this->pengguna->getRole() !== $role) {
            $this->redirect($this->pengguna->urlDashboard());
        }

        // semua form POST wajib membawa token CSRF yang benar
        if ($this->isPost()) {
            $this->cekCsrf();
        }
        return $this->pengguna;
    }

    // Token CSRF mencegah situs lain diam-diam mengirim form atas nama pengguna yang sedang login.
    protected function cekCsrf(): void
    {
        $token = $this->post('csrf');
        if (!hash_equals(csrfToken(), $token)) {
            if (($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json' || $this->aksi() === 'ubah_status') {
                $this->json(['sukses' => false, 'pesan' => 'Sesi kedaluwarsa, muat ulang halaman.']);
            }
            $this->flash('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
            $this->redirect(basename($_SERVER['SCRIPT_NAME']));
        }
    }
}
