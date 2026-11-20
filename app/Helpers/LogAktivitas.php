<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

// Log aktivitas disimpan di FILE (materi Operasi File / File Handling),
// bukan di database. Lokasi: storage/logs/aktivitas.log
// Format satu baris: waktu|pelaku|aksi|detail
class LogAktivitas
{
    public static function lokasiFile()
    {
        return storage_path('logs/aktivitas.log');
    }

    public static function catat($aksi, $detail = '', $pelaku = null)
    {
        if ($pelaku === null) {
            $pelaku = Auth::check() ? Auth::user()->username : 'tamu';
        }

        // Tanda | dan enter dihapus supaya format baris tidak rusak
        $bersihkan = ['|', "\r", "\n"];
        $baris = date('Y-m-d H:i:s') . '|'
            . str_replace($bersihkan, ' ', $pelaku) . '|'
            . str_replace($bersihkan, ' ', $aksi) . '|'
            . str_replace($bersihkan, ' ', $detail) . "\n";

        $file = @fopen(self::lokasiFile(), 'a'); // mode a = tulis di akhir file
        if (!$file) {
            return;
        }
        if (flock($file, LOCK_EX)) { // kunci file supaya tidak ditulis bersamaan
            fwrite($file, $baris);
            flock($file, LOCK_UN);
        }
        fclose($file);
    }

    // Ambil catatan terbaru (paling baru di atas)
    public static function baca($maks = 200)
    {
        $hasil = [];
        if (!file_exists(self::lokasiFile())) {
            return $hasil;
        }

        $file = fopen(self::lokasiFile(), 'r'); // mode r = baca saja
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

        return array_reverse($hasil);
    }

    public static function ukuran()
    {
        if (!file_exists(self::lokasiFile())) {
            return 0;
        }
        return filesize(self::lokasiFile());
    }

    public static function kosongkan()
    {
        $file = fopen(self::lokasiFile(), 'w'); // mode w = isi lama dihapus
        fclose($file);
    }
}
