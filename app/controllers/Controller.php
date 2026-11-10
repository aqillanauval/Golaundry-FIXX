<?php
class Controller
{
    protected $pengguna = null; 
    protected function tampilkan($namaView, $data = [])
    {
        extract($data); 
        $pengguna = $this->pengguna;
        include FOLDER_APP . '/views/' . $namaView . '.php';
    }

    protected function kirimJson($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function adaPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    protected function post($nama)
    {
        if (isset($_POST[$nama]) && is_string($_POST[$nama])) {
            return trim($_POST[$nama]);
        }
        return '';
    }

    protected function get($nama)
    {
        if (isset($_GET[$nama]) && is_string($_GET[$nama])) {
            return trim($_GET[$nama]);
        }
        return '';
    }

    protected function pindahKe($halaman)
    {
        header('Location: ' . $halaman);
        exit;
    }

    protected function pesan($tipe, $isi)
    {
        $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $isi];
    }

    protected function harusLogin($role = null)
    {
        if (!isset($_SESSION['user_id'])) {
            $this->pesan('error', 'Login dulu ya.');
            $this->pindahKe('login.php');
        }

        $userModel = new UserModel();
        $this->pengguna = $userModel->cariById($_SESSION['user_id']);
        if ($this->pengguna == null) {
            $_SESSION = [];
            $this->pesan('error', 'Akun kamu udah nggak terdaftar.');
            $this->pindahKe('login.php');
        }

        if ($role != null && $this->pengguna->getRole() != $role) {
            $this->pindahKe($this->pengguna->getHalamanDashboard());
        }

        if ($this->adaPost()) {
            $this->cekToken();
        }
        return $this->pengguna;
    }

    protected function cekToken()
    {
        if (tokenCocok($this->post('token'))) {
            return; 
        }
        if ($this->post('aksi') == 'ubah_status') {
            $this->kirimJson(['sukses' => false, 'pesan' => 'Sesi habis, refresh halamannya dulu ya.']);
        }
        $this->pesan('error', 'Form-nya kedaluwarsa, coba kirim lagi ya.');
        $this->pindahKe(basename($_SERVER['SCRIPT_NAME']));
    }
}
