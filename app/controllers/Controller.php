<?php
// ============================================================
// Controller.php  ->  class INDUK buat semua controller
//
// Alur MVC di project ini (sama kayak index.php -> c_programKerja di Modul 12):
//   1. Browser buka layanan.php
//   2. layanan.php bikin  $controller = new LayananController();  terus  invoke()
//   3. Controller baca input user, nyuruh MODEL ambil/simpan data di database
//   4. Controller ngirim datanya ke VIEW (file HTML) buat ditampilin
// ============================================================

class Controller
{
    protected $pengguna = null; // objek Admin / Pelanggan yang lagi login

    // ---------- VIEW ----------

    // tampilin file di folder app/views/
    // $data = ['judulHalaman' => 'Layanan']  ->  di view bisa dipakai jadi $judulHalaman
    protected function tampilkan($namaView, $data = [])
    {
        extract($data); // ubah isi array jadi variabel biasa
        $pengguna = $this->pengguna;
        include FOLDER_APP . '/views/' . $namaView . '.php';
    }

    // balasan buat AJAX (fetch di JavaScript) dalam bentuk JSON
    protected function kirimJson($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // ---------- baca input ----------

    protected function adaPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    // ambil isi $_POST['nama'], kalau nggak ada hasilnya ''
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

    // ---------- pindah halaman & pesan ----------

    protected function pindahKe($halaman)
    {
        header('Location: ' . $halaman);
        exit;
    }

    // pesan yang muncul sekali di halaman berikutnya (disimpan di SESSION)
    protected function pesan($tipe, $isi)
    {
        $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $isi];
    }

    // ---------- keamanan ----------

    // dipanggil di awal halaman yang harus login dulu
    // $role: 'admin' / 'customer' kalau halamannya khusus role tertentu
    protected function harusLogin($role = null)
    {
        if (!isset($_SESSION['user_id'])) {
            $this->pesan('error', 'Login dulu ya.');
            $this->pindahKe('login.php');
        }

        // ambil data terbaru dari database (kalau akunnya udah dihapus, langsung logout)
        $userModel = new UserModel();
        $this->pengguna = $userModel->cariById($_SESSION['user_id']);
        if ($this->pengguna == null) {
            $_SESSION = [];
            $this->pesan('error', 'Akun kamu udah nggak terdaftar.');
            $this->pindahKe('login.php');
        }

        // pelanggan nyasar ke halaman admin (atau sebaliknya) -> balikin ke dashboard-nya
        if ($role != null && $this->pengguna->getRole() != $role) {
            $this->pindahKe($this->pengguna->getHalamanDashboard());
        }

        // semua form POST wajib bawa token yang cocok
        if ($this->adaPost()) {
            $this->cekToken();
        }
        return $this->pengguna;
    }

    protected function cekToken()
    {
        if (tokenCocok($this->post('token'))) {
            return; // aman
        }
        if ($this->post('aksi') == 'ubah_status') {
            $this->kirimJson(['sukses' => false, 'pesan' => 'Sesi habis, refresh halamannya dulu ya.']);
        }
        $this->pesan('error', 'Form-nya kedaluwarsa, coba kirim lagi ya.');
        $this->pindahKe(basename($_SERVER['SCRIPT_NAME']));
    }
}
