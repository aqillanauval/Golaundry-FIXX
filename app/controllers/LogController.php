<?php
// LogController.php  ->  halaman "Log Aktivitas" (khusus admin)
// Datanya dibaca dari file storage/logs/aktivitas.log (lihat app/log.php)

class LogController extends Controller
{
    public function invoke()
    {
        $this->harusLogin('admin');

        if ($this->adaPost() && $this->post('aksi') == 'kosongkan') {
            kosongkanLog();
            catatLog('Kosongkan log aktivitas');
            $this->pesan('sukses', 'Log aktivitas udah dikosongin.');
            $this->pindahKe('log.php');
        }

        $this->tampilkan('log/index', [
            'judulHalaman' => 'Log Aktivitas',
            'subJudul'     => 'Siapa ngapain, kapan. Disimpan di file storage/logs/aktivitas.log.',
            'catatan'      => bacaLog(300),
            'ukuran'       => ukuranLog(),
        ]);
    }
}
