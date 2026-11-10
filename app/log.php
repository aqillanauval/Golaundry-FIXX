<?php
// ============================================================
// log.php  ->  catatan aktivitas disimpan di FILE  (Modul 9: Operasi File)
//
// Data utama udah di database, tapi file tetap berguna buat catatan kecil kayak log.
// Lokasinya: storage/logs/aktivitas.log
// Satu baris = satu aktivitas, dipisah tanda "|":
//   2026-11-10 12:30:01|admin@golaundry.com|Tambah layanan|Cuci Kering
//
// Fungsi file yang dipakai:
//   file_exists(), mkdir()     -> cek & bikin folder
//   fopen('a'), flock(), fwrite(), fclose()  -> nambah baris di akhir file
//   fopen('r'), fgets()        -> baca baris per baris
//   filesize()                 -> ukuran file
//   fopen('w')                 -> ngosongin file
// ============================================================

function fileLog()
{
    return FOLDER_LOG . 'aktivitas.log';
}

// catat satu aktivitas. $pelaku kosong = ambil dari user yang lagi login
function catatLog($aksi, $detail = '', $pelaku = null)
{
    if (!file_exists(FOLDER_LOG)) {
        mkdir(FOLDER_LOG, 0775, true);
    }
    if ($pelaku === null) {
        $pelaku = isset($_SESSION['user']) ? $_SESSION['user'] : 'tamu';
    }

    // tanda | dan enter dibuang biar format barisnya nggak rusak
    $bersihin = ['|', "\r", "\n"];
    $baris = date('Y-m-d H:i:s') . '|'
           . str_replace($bersihin, ' ', $pelaku) . '|'
           . str_replace($bersihin, ' ', $aksi) . '|'
           . str_replace($bersihin, ' ', $detail) . "\n";

    $file = @fopen(fileLog(), 'a'); // mode 'a' = nambah di akhir, isi lama tetap ada
    if (!$file) {
        error_log('File log nggak bisa dibuka: ' . fileLog());
        return;
    }
    // kunci file dulu, biar kalau ada 2 orang nulis barengan isinya nggak tabrakan
    if (flock($file, LOCK_EX)) {
        fwrite($file, $baris);
        flock($file, LOCK_UN); // buka kuncinya lagi
    }
    fclose($file);
}

// baca log, ambil $maks baris terakhir, yang paling baru di atas
function bacaLog($maks = 200)
{
    $hasil = [];
    if (!file_exists(fileLog())) {
        return $hasil;
    }

    $file = fopen(fileLog(), 'r'); // mode 'r' = cuma baca
    while (($baris = fgets($file)) !== false) {
        $baris = trim($baris);
        if ($baris == '') {
            continue;
        }
        $kolom = explode('|', $baris, 4);
        if (count($kolom) == 4) {
            $hasil[] = [
                'waktu'  => $kolom[0],
                'pelaku' => $kolom[1],
                'aksi'   => $kolom[2],
                'detail' => $kolom[3],
            ];
        }
        if (count($hasil) > $maks) {
            array_shift($hasil); // buang yang paling lama
        }
    }
    fclose($file);

    return array_reverse($hasil); // dibalik biar yang terbaru di atas
}

function ukuranLog()
{
    if (!file_exists(fileLog())) {
        return 0;
    }
    return filesize(fileLog());
}

function kosongkanLog()
{
    $file = fopen(fileLog(), 'w'); // mode 'w' = nulis dari awal, isi lama kehapus
    fclose($file);
}
