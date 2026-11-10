<?php
// ============================================================
// helpers.php  -  Fungsi bantu kecil yang dipakai banyak view & controller.
// (Fungsi yang berhubungan dengan data sudah pindah ke kelas Model.)
// ============================================================

// aman ditampilkan di HTML (mencegah XSS)
function e($teks): string
{
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

function rupiah($angka): string
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

// rapikan teks input: buang spasi di tepi & karakter kontrol, batasi panjang
function bersihkan(string $teks, int $maks = 255): string
{
    $teks = trim(preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $teks) ?? '');
    return function_exists('mb_substr') ? mb_substr($teks, 0, $maks) : substr($teks, 0, $maks);
}

// 3.0 -> "3", 2.5 -> "2,5"
function formatBerat($berat): string
{
    $b = round((float)$berat, 1);
    return ($b == floor($b)) ? (string)(int)$b : number_format($b, 1, ',', '');
}

function tanggalIndo(): string
{
    $hari  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
              'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return $hari[(int)date('w')] . ', ' . date('j') . ' ' . $bulan[(int)date('n')] . ' ' . date('Y');
}

// tanggal pendek: "13 Okt 2026" (atau "13 Okt 2026 09:30" kalau $denganJam)
// $waktu boleh timestamp (int) atau teks tanggal dari database
function tglPendek($waktu = null, bool $denganJam = false): string
{
    if (is_string($waktu)) {
        $waktu = strtotime($waktu);
    }
    $waktu = $waktu ?: time();
    $bln = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $hasil = date('d', $waktu) . ' ' . $bln[(int)date('n', $waktu)] . ' ' . date('Y', $waktu);
    return $denganJam ? $hasil . ' ' . date('H:i', $waktu) : $hasil;
}

function sapaan(): string
{
    $jam = (int)date('G');
    if ($jam < 11) { return 'Selamat pagi'; }
    if ($jam < 15) { return 'Selamat siang'; }
    if ($jam < 18) { return 'Selamat sore'; }
    return 'Selamat malam';
}

function inisial(string $nama): string
{
    $huruf = function_exists('mb_substr') ? mb_substr($nama, 0, 1) : substr($nama, 0, 1);
    return function_exists('mb_strtoupper') ? mb_strtoupper($huruf) : strtoupper($huruf);
}

// email dijadikan huruf kecil, nomor HP dibiarkan
function normalUser(string $username): string
{
    $username = trim($username);
    return (strpos($username, '@') !== false) ? strtolower($username) : $username;
}

function usernameValid(string $username): bool
{
    return filter_var($username, FILTER_VALIDATE_EMAIL) !== false
        || preg_match('/^\+?[0-9]{9,15}$/', $username) === 1;
}

// ---------- CSRF ----------

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

// ---------- Tampilan kecil ----------

function tampilFlash(): string
{
    if (!isset($_SESSION['flash'])) {
        return '';
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $kelas = ($f['tipe'] === 'error') ? 'alert-error' : 'alert-sukses';
    return '<div class="alert ' . $kelas . '" data-autohide>' . e($f['pesan']) . '</div>';
}

// "Menunggu Jemput" -> "menunggu-jemput" (nama class CSS)
function statusKelas(string $status): string
{
    return strtolower(str_replace(' ', '-', $status));
}

function badgeStatus(string $status): string
{
    return '<span class="badge badge-' . e(statusKelas($status)) . '">' . e($status) . '</span>';
}

function formHapus($nilai, string $pesanKonfirmasi, string $namaField = 'id', string $teksTombol = 'Hapus'): string
{
    return '<form method="post" class="inline" data-konfirmasi="' . e($pesanKonfirmasi) . '">'
         . csrfField()
         . '<input type="hidden" name="aksi" value="hapus">'
         . '<input type="hidden" name="' . e($namaField) . '" value="' . e($nilai) . '">'
         . '<button type="submit" class="btn btn-sm btn-danger">' . e($teksTombol) . '</button>'
         . '</form>';
}

// ikon memakai Font Awesome (kit dipasang di <head> tiap halaman)
function ikon(string $nama, int $ukuran = 20): string
{
    $kelas = [
        'home' => 'fa-house', 'tag' => 'fa-tag', 'users' => 'fa-users', 'user' => 'fa-user',
        'file' => 'fa-file-lines', 'briefcase' => 'fa-briefcase', 'gift' => 'fa-gift',
        'plus' => 'fa-circle-plus', 'plusplain' => 'fa-plus', 'minus' => 'fa-minus',
        'clock' => 'fa-clock', 'logout' => 'fa-right-from-bracket', 'menu' => 'fa-bars',
        'mail' => 'fa-envelope', 'lock' => 'fa-lock', 'eye' => 'fa-eye', 'eyeoff' => 'fa-eye-slash',
        'search' => 'fa-magnifying-glass', 'arrow' => 'fa-arrow-right', 'printer' => 'fa-print',
        'cart' => 'fa-cart-shopping', 'wallet' => 'fa-wallet', 'check' => 'fa-check',
        'image' => 'fa-image', 'percent' => 'fa-percent', 'shirt' => 'fa-shirt',
        'droplet' => 'fa-droplet', 'truck' => 'fa-truck', 'scale' => 'fa-scale-balanced',
        'store' => 'fa-store', 'pin' => 'fa-location-dot', 'pen' => 'fa-pen',
        'list' => 'fa-list-ul', 'database' => 'fa-database', 'trash' => 'fa-trash-can',
    ];
    $fa = $kelas[$nama] ?? 'fa-circle';
    $px = round($ukuran * 0.85);
    return '<i class="fa-solid ' . $fa . ' fa-fw ikon-fa" style="font-size:' . $px . 'px" aria-hidden="true"></i>';
}

function logoSvg(int $ukuran = 44): string
{
    return '<img src="assets/img/logo.png" width="' . $ukuran . '" height="' . $ukuran . '" alt="Logo GOLaundry" class="logo-img">';
}
