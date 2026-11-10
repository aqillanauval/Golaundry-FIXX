<?php
// ============================================================
// Logger.php  -  Log aktivitas disimpan di FILE (Modul 9: Operasi File)
//
// Data utama sekarang ada di database, tapi file tetap berguna untuk
// catatan kecil seperti log. Satu baris = satu aktivitas, dipisah "|":
//   2026-11-10 12:30:01|admin@golaundry.com|Tambah layanan|Cuci Kering
//
// Fungsi file yang dipakai:
//   mkdir(), file_exists()        -> siapkan folder & file
//   fopen('a') + flock(LOCK_EX)   -> tambah baris di akhir file, dikunci
//   fwrite(), fclose()            -> tulis lalu tutup
//   fopen('r') + fgets()          -> baca baris per baris
//   filesize()                    -> ukuran file
//   fopen('w')                    -> kosongkan file
// ============================================================

class Logger
{
    private static function path(): string
    {
        return FOLDER_LOG . 'aktivitas.log';
    }

    // pastikan folder log ada
    private static function siapkanFolder(): void
    {
        if (!file_exists(FOLDER_LOG)) {
            mkdir(FOLDER_LOG, 0775, true);
        }
    }

    // "|" dan enter dibuang supaya format baris tidak rusak
    private static function bersih(string $teks): string
    {
        return trim(str_replace(['|', "\r", "\n"], ' ', $teks));
    }

    // $pelaku kosong = diambil dari session (pengguna yang sedang login)
    public static function catat(string $aksi, string $detail = '', ?string $pelaku = null): void
    {
        self::siapkanFolder();
        $pelaku = $pelaku ?? ($_SESSION['user'] ?? 'tamu');
        $baris = date('Y-m-d H:i:s') . '|' . self::bersih($pelaku) . '|'
               . self::bersih($aksi) . '|' . self::bersih($detail) . "\n";

        $fh = @fopen(self::path(), 'a'); // 'a' = append, isi lama tidak hilang
        if ($fh === false) {
            error_log('Log aktivitas tidak bisa dibuka: ' . self::path());
            return;
        }
        // kunci file supaya dua request yang menulis bersamaan tidak saling menimpa
        if (flock($fh, LOCK_EX)) {
            fwrite($fh, $baris);
            fflush($fh);
            flock($fh, LOCK_UN);
        }
        fclose($fh);
    }

    // baca $maks baris terakhir, yang terbaru di atas
    public static function baca(int $maks = 200): array
    {
        if (!file_exists(self::path())) {
            return [];
        }
        $hasil = [];
        $fh = fopen(self::path(), 'r'); // 'r' = hanya baca
        if ($fh === false) {
            return [];
        }
        while (($baris = fgets($fh)) !== false) {
            $baris = trim($baris);
            if ($baris === '') {
                continue;
            }
            $kolom = explode('|', $baris, 4);
            if (count($kolom) === 4) {
                $hasil[] = ['waktu' => $kolom[0], 'pelaku' => $kolom[1], 'aksi' => $kolom[2], 'detail' => $kolom[3]];
            }
            if (count($hasil) > $maks) {
                array_shift($hasil); // simpan hanya $maks baris terakhir
            }
        }
        fclose($fh);
        return array_reverse($hasil);
    }

    public static function ukuran(): int
    {
        return file_exists(self::path()) ? filesize(self::path()) : 0;
    }

    public static function kosongkan(): void
    {
        self::siapkanFolder();
        $fh = fopen(self::path(), 'w'); // 'w' = tulis dari awal (isi lama terhapus)
        if ($fh !== false) {
            fclose($fh);
        }
    }
}
