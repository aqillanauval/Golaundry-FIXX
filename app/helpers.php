<?php
// Fungsi bantu yang bisa dipanggil dari mana saja (controller & Blade).
// Pindahan dari app/fungsi.php di proyek PHP Native.
// File ini di-load di AppServiceProvider.

if (!function_exists('rupiah')) {
    function rupiah($angka)
    {
        return 'Rp ' . number_format((float) $angka, 0, ',', '.');
    }
}

if (!function_exists('formatBerat')) {
    // 3.0 -> "3", 2.5 -> "2,5"
    function formatBerat($berat)
    {
        $berat = round((float) $berat, 1);
        if ($berat == floor($berat)) {
            return (string) (int) $berat;
        }
        return number_format($berat, 1, ',', '');
    }
}

if (!function_exists('namaBulan')) {
    function namaBulan($nomor, $pendek = false)
    {
        $panjang = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
                    'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $singkat = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return $pendek ? $singkat[(int) $nomor] : $panjang[(int) $nomor];
    }
}

if (!function_exists('tanggalIndo')) {
    // Contoh: Jumat, 20 November 2026
    function tanggalIndo()
    {
        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $hari[date('w')] . ', ' . date('j') . ' ' . namaBulan(date('n')) . ' ' . date('Y');
    }
}

if (!function_exists('tglPendek')) {
    // Contoh: 20 Nov 2026 14:30
    function tglPendek($tanggal, $pakaiJam = false)
    {
        $waktu = strtotime((string) $tanggal);
        $hasil = date('d', $waktu) . ' ' . namaBulan(date('n', $waktu), true) . ' ' . date('Y', $waktu);
        if ($pakaiJam) {
            $hasil .= ' ' . date('H:i', $waktu);
        }
        return $hasil;
    }
}

if (!function_exists('sapaan')) {
    function sapaan()
    {
        $jam = (int) date('G');
        if ($jam < 11) return 'Selamat pagi';
        if ($jam < 15) return 'Selamat siang';
        if ($jam < 18) return 'Selamat sore';
        return 'Selamat malam';
    }
}

if (!function_exists('inisial')) {
    function inisial($nama)
    {
        return mb_strtoupper(mb_substr((string) $nama, 0, 1));
    }
}

if (!function_exists('normalUser')) {
    // Email disimpan huruf kecil semua, nomor HP dibiarkan
    function normalUser($username)
    {
        $username = trim((string) $username);
        if (strpos($username, '@') !== false) {
            return strtolower($username);
        }
        return $username;
    }
}

if (!function_exists('usernameValid')) {
    // Username boleh email yang valid ATAU nomor HP 9-15 angka
    function usernameValid($username)
    {
        $emailValid = filter_var($username, FILTER_VALIDATE_EMAIL) !== false;
        $hpValid = preg_match('/^\+?[0-9]{9,15}$/', $username) === 1;
        return $emailValid || $hpValid;
    }
}

if (!function_exists('statusKelas')) {
    // "Menunggu Jemput" -> "menunggu-jemput" (dipakai untuk class CSS)
    function statusKelas($status)
    {
        return strtolower(str_replace(' ', '-', $status));
    }
}
