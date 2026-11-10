<?php
// ============================================================
// fungsi.php  ->  kumpulan fungsi kecil yang dipakai di banyak halaman
// (urusan database udah pindah ke class Model, di sini cuma fungsi bantu)
// ============================================================

// ---------- tampilan ----------

// biar teks aman ditampilin di HTML (mencegah XSS)
function e($teks)
{
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

// 15000 -> "Rp 15.000"
function rupiah($angka)
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

// 3.0 -> "3", 2.5 -> "2,5"
function formatBerat($berat)
{
    $berat = round((float)$berat, 1);
    if ($berat == floor($berat)) {
        return (string)(int)$berat;
    }
    return number_format($berat, 1, ',', '');
}

// rapihin input: buang spasi di depan/belakang & karakter aneh, terus batasi panjangnya
function bersihkan($teks, $maks = 255)
{
    $teks = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', (string)$teks);
    return mb_substr(trim($teks), 0, $maks);
}

// "Selasa, 10 November 2026"
function tanggalIndo()
{
    $hari  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
              'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return $hari[date('w')] . ', ' . date('j') . ' ' . $bulan[date('n')] . ' ' . date('Y');
}

// "2026-10-13 09:30:00" -> "13 Okt 2026" (atau "13 Okt 2026 09:30" kalau $pakaiJam = true)
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

// sapaan sesuai jam sekarang
function sapaan()
{
    $jam = date('G');
    if ($jam < 11) return 'Selamat pagi';
    if ($jam < 15) return 'Selamat siang';
    if ($jam < 18) return 'Selamat sore';
    return 'Selamat malam';
}

// "Rina" -> "R" (buat avatar bulat)
function inisial($nama)
{
    return mb_strtoupper(mb_substr($nama, 0, 1));
}

// ---------- validasi username ----------

// email dijadiin huruf kecil semua, nomor HP dibiarin
function normalUser($username)
{
    $username = trim($username);
    if (strpos($username, '@') !== false) {
        return strtolower($username);
    }
    return $username;
}

// username boleh email ATAU nomor HP 9-15 angka
function usernameValid($username)
{
    $emailValid = filter_var($username, FILTER_VALIDATE_EMAIL) !== false;
    $hpValid = preg_match('/^\+?[0-9]{9,15}$/', $username) === 1;
    return $emailValid || $hpValid;
}

// ---------- token form (CSRF) ----------
// Token acak disimpan di SESSION dan ditaruh di setiap form.
// Waktu form dikirim, token dicocokin. Kalau beda = form palsu dari web lain, ditolak.

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

// ---------- pesan flash (muncul sekali, abis itu hilang) ----------

function tampilFlash()
{
    if (!isset($_SESSION['flash'])) {
        return '';
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']); // hapus biar nggak muncul lagi

    $kelas = ($flash['tipe'] == 'error') ? 'alert-error' : 'alert-sukses';
    return '<div class="alert ' . $kelas . '" data-autohide>' . e($flash['pesan']) . '</div>';
}

// ---------- status pesanan ----------

// "Menunggu Jemput" -> "menunggu-jemput" (dipakai buat nama class CSS)
function statusKelas($status)
{
    return strtolower(str_replace(' ', '-', $status));
}

function badgeStatus($status)
{
    return '<span class="badge badge-' . e(statusKelas($status)) . '">' . e($status) . '</span>';
}

// ---------- promo ----------

function promoMasihBerlaku($promo)
{
    // format tanggal Y-m-d bisa langsung dibandingin kayak teks
    return $promo['berlaku_sampai'] >= date('Y-m-d');
}

function adaFotoPromo($promo)
{
    return $promo['foto'] != '' && file_exists(FOLDER_UPLOAD . $promo['foto']);
}

// ---------- potongan HTML yang sering dipakai ----------

// tombol hapus kecil (form POST + konfirmasi)
function formHapus($nilai, $pesanKonfirmasi, $teksTombol = 'Hapus')
{
    return '<form method="post" class="inline" data-konfirmasi="' . e($pesanKonfirmasi) . '">'
         . inputToken()
         . '<input type="hidden" name="aksi" value="hapus">'
         . '<input type="hidden" name="id" value="' . e($nilai) . '">'
         . '<button type="submit" class="btn btn-sm btn-danger">' . e($teksTombol) . '</button>'
         . '</form>';
}

// ikon dari Font Awesome (kit-nya dipasang di <head> tiap halaman)
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
