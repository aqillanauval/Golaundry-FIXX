<?php
// ============================================================
// LogController.php  -  Halaman "Log Aktivitas" (khusus admin)
// Membaca file storage/logs/aktivitas.log lewat kelas Logger (Modul 9).
// ============================================================

class LogController extends Controller
{
    public function invoke(): void
    {
        $this->wajibLogin('admin');

        if ($this->isPost() && $this->aksi() === 'kosongkan') {
            Logger::kosongkan();
            Logger::catat('Kosongkan log aktivitas');
            $this->flash('sukses', 'Log aktivitas dikosongkan.');
            $this->redirect('log.php');
        }

        $this->view('log/index', [
            'judulHalaman' => 'Log Aktivitas',
            'subJudul'     => 'Catatan siapa melakukan apa. Disimpan di file storage/logs/aktivitas.log.',
            'catatan'      => Logger::baca(300),
            'ukuran'       => Logger::ukuran(),
        ]);
    }
}
