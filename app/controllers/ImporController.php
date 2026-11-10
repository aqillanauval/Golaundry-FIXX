<?php
// ImporController.php  ->  pindahin data lama dari file .txt ke database (khusus admin)

class ImporController extends Controller
{
    public function invoke()
    {
        $this->harusLogin('admin');
        $model = new ImporModel();
        $hasil = null;
        $error = '';

        if ($this->adaPost() && $this->post('aksi') == 'impor') {
            try {
                $hasil = $model->jalankan();

                $ringkas = [];
                foreach ($hasil as $tabel => $jumlah) {
                    $ringkas[] = "$tabel: $jumlah";
                }
                catatLog('Impor data .txt', implode(', ', $ringkas));
            } catch (Exception $e) {
                error_log('Impor gagal: ' . $e->getMessage());
                $error = 'Impor dibatalin (rollback), jadi nggak ada data yang masuk. Detail error-nya ada di log server.';
            }
        }

        $this->tampilkan('impor/index', [
            'judulHalaman' => 'Impor Data Lama',
            'subJudul'     => 'Pindahin isi file .txt di folder data/ ke database. Cukup sekali aja.',
            'ringkasan'    => $model->ringkasan(),
            'hasil'        => $hasil,
            'error'        => $error,
        ]);
    }
}
