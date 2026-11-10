<?php
<<<<<<< HEAD
=======

>>>>>>> 22a935c (update fitur)
function e($teks)
{
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

function rupiah($angka)
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function formatBerat($berat)
{
    $berat = round((float)$berat, 1);
    if ($berat == floor($berat)) {
        return (string)(int)$berat;
    }
    return number_format($berat, 1, ',', '');
}

function bersihkan($teks, $maks = 255)
{
    $teks = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', (string)$teks);
    return mb_substr(trim($teks), 0, $maks);
}

function tanggalIndo()
{
    $hari  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
              'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return $hari[date('w')] . ', ' . date('j') . ' ' . $bulan[date('n')] . ' ' . date('Y');
}

function tglPendek($tanggal = null, $pakaiJam = false)
{
    $waktu = $tanggal ? strtotime($tanggal) : time();
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $hasil = date('d', $waktu) . ' ' . $bulan[date('n', $waktu)] . ' ' . date('Y', $waktu);
    if ($pakaiJam) {
        $hasil .= ' ' . date('H:i', $waktu);
    }
    return $hasil;
}

function sapaan()
{
    $jam = date('G');
    if ($jam < 11) return 'Selamat pagi';
    if ($jam < 15) return 'Selamat siang';
    if ($jam < 18) return 'Selamat sore';
    return 'Selamat malam';
}

function inisial($nama)
{
    return mb_strtoupper(mb_substr($nama, 0, 1));
}

<<<<<<< HEAD

=======
>>>>>>> 22a935c (update fitur)
function normalUser($username)
{
    $username = trim($username);
    if (strpos($username, '@') !== false) {
        return strtolower($username);
    }
    return $username;
}

function usernameValid($username)
{
    $emailValid = filter_var($username, FILTER_VALIDATE_EMAIL) !== false;
    $hpValid = preg_match('/^\+?[0-9]{9,15}$/', $username) === 1;
    return $emailValid || $hpValid;
}

function ambilToken()
{
    if (empty($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['token'];
}

function inputToken()
{
    return '<input type="hidden" name="token" value="' . e(ambilToken()) . '">';
}

function tokenCocok($tokenDariForm)
{
    return is_string($tokenDariForm) && hash_equals(ambilToken(), $tokenDariForm);
}

<<<<<<< HEAD

=======
>>>>>>> 22a935c (update fitur)
function tampilFlash()
{
    if (!isset($_SESSION['flash'])) {
        return '';
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']); 

    $kelas = ($flash['tipe'] == 'error') ? 'alert-error' : 'alert-sukses';
    return '<div class="alert ' . $kelas . '" data-autohide>' . e($flash['pesan']) . '</div>';
}

<<<<<<< HEAD

=======
>>>>>>> 22a935c (update fitur)
function statusKelas($status)
{
    return strtolower(str_replace(' ', '-', $status));
}

function badgeStatus($status)
{
    return '<span class="badge badge-' . e(statusKelas($status)) . '">' . e($status) . '</span>';
}

<<<<<<< HEAD

=======
>>>>>>> 22a935c (update fitur)
function promoMasihBerlaku($promo)
{
    return $promo['berlaku_sampai'] >= date('Y-m-d');
}

function adaFotoPromo($promo)
{
    return $promo['foto'] != '' && file_exists(FOLDER_UPLOAD . $promo['foto']);
}

<<<<<<< HEAD

=======
>>>>>>> 22a935c (update fitur)
function formHapus($nilai, $pesanKonfirmasi, $teksTombol = 'Hapus')
{
    return '<form method="post" class="inline" data-konfirmasi="' . e($pesanKonfirmasi) . '">'
         . inputToken()
         . '<input type="hidden" name="aksi" value="hapus">'
         . '<input type="hidden" name="id" value="' . e($nilai) . '">'
         . '<button type="submit" class="btn btn-sm btn-danger">' . e($teksTombol) . '</button>'
         . '</form>';
}

function ikon($nama, $ukuran = 20)
{
    $daftar = [
        'home' => 'fa-house', 'tag' => 'fa-tag', 'users' => 'fa-users', 'user' => 'fa-user',
        'file' => 'fa-file-lines', 'briefcase' => 'fa-briefcase', 'gift' => 'fa-gift',
        'plus' => 'fa-circle-plus', 'plusplain' => 'fa-plus', 'clock' => 'fa-clock',
        'logout' => 'fa-right-from-bracket', 'menu' => 'fa-bars', 'mail' => 'fa-envelope',
        'lock' => 'fa-lock', 'eye' => 'fa-eye', 'eyeoff' => 'fa-eye-slash', 'arrow' => 'fa-arrow-right',
        'printer' => 'fa-print', 'cart' => 'fa-cart-shopping', 'wallet' => 'fa-wallet',
        'check' => 'fa-check', 'percent' => 'fa-percent', 'shirt' => 'fa-shirt',
        'droplet' => 'fa-droplet', 'truck' => 'fa-truck', 'scale' => 'fa-scale-balanced',
        'store' => 'fa-store', 'pin' => 'fa-location-dot', 'list' => 'fa-list-ul',
        'database' => 'fa-database', 'trash' => 'fa-trash-can',
    ];
    $kelas = isset($daftar[$nama]) ? $daftar[$nama] : 'fa-circle';
    $px = round($ukuran * 0.85);
    return '<i class="fa-solid ' . $kelas . ' fa-fw ikon-fa" style="font-size:' . $px . 'px" aria-hidden="true"></i>';
}

function logoImg($ukuran = 44)
{
    return '<img src="assets/img/logo.png" width="' . $ukuran . '" height="' . $ukuran . '" alt="Logo GOLaundry" class="logo-img">';
}
