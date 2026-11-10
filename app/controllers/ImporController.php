<?php
// ============================================================
// ImporController.php  -  Impor data lama dari file .txt ke database (admin)
// ============================================================

class ImporController extends Controller
{
    public function invoke(): void
    {
        $this->wajibLogin('admin');
        $model = new ImporModel();
        $hasil = null;
        $error = '';

        if ($this->isPost() && $this->aksi() === 'impor') {
            try {
                $hasil = $model->jalankanImpor();
                $teks = [];
                foreach ($hasil as $tabel => $n) {
                    $teks[] = "$tabel: $n";
                }
                Logger::catat('Impor data .txt', implode(', ', $teks));
            } catch (Throwable $e) {
                error_log('Impor gagal: ' . $e->getMessage());
                $error = 'Impor dibatalkan (rollback), tidak ada data yang tersimpan. Detail kesalahan ada di log server.';
            }
        }

        $this->view('impor/index', [
            'judulHalaman' => 'Impor Data Lama',
            'subJudul'     => 'Memindahkan isi file .txt di folder data/ ke database. Cukup dijalankan sekali.',
            'ringkasan'    => $model->ringkasan(),
            'hasil'        => $hasil,
            'error'        => $error,
        ]);
    }
}
