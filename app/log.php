<?php
function fileLog()
{
    return FOLDER_LOG . 'aktivitas.log';
}

function catatLog($aksi, $detail = '', $pelaku = null)
{
    if (!file_exists(FOLDER_LOG)) {
        mkdir(FOLDER_LOG, 0775, true);
    }
    if ($pelaku === null) {
        $pelaku = isset($_SESSION['user']) ? $_SESSION['user'] : 'tamu';
    }

    $bersihin = ['|', "\r", "\n"];
    $baris = date('Y-m-d H:i:s') . '|'
           . str_replace($bersihin, ' ', $pelaku) . '|'
           . str_replace($bersihin, ' ', $aksi) . '|'
           . str_replace($bersihin, ' ', $detail) . "\n";

    $file = @fopen(fileLog(), 'a'); 
    if (!$file) {
        error_log('File log nggak bisa dibuka: ' . fileLog());
        return;
    }
    if (flock($file, LOCK_EX)) {
        fwrite($file, $baris);
        flock($file, LOCK_UN);
    }
    fclose($file);
}

function bacaLog($maks = 200)
{
    $hasil = [];
    if (!file_exists(fileLog())) {
        return $hasil;
    }

    $file = fopen(fileLog(), 'r'); 
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
            array_shift($hasil); 
        }
    }
    fclose($file);

    return array_reverse($hasil); 
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
    $file = fopen(fileLog(), 'w'); 
    fclose($file);
}
