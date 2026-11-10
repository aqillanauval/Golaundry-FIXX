<?php
// ============================================================
// LayananController.php  ->  halaman CRUD Layanan (khusus admin)
//   Create : isi form "Tambah Layanan"
//   Read   : tabel daftar layanan
//   Update : klik Edit (layanan.php?edit=ID) -> form keisi -> Simpan
//   Delete : tombol Hapus
// ============================================================

class LayananController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new LayananModel();
    }

    public function invoke()
    {
        $this->harusLogin('admin');

        $error = '';
        $form = ['id' => '', 'nama' => '', 'harga' => '', 'satuan' => 'kg']; // form kosong = mode tambah

        // ----- CREATE & UPDATE -----
        if ($this->adaPost() && $this->post('aksi') == 'simpan') {
            $form['id'] = $this->post('id');
            $form['nama'] = bersihkan($this->post('nama'), 100);
            $form['harga'] = (int)$this->post('harga');
            $form['satuan'] = bersihkan($this->post('satuan'), 20);

            if ($form['nama'] == '' || $form['harga'] <= 0 || $form['satuan'] == '') {
                $error = 'Nama, harga, sama satuan wajib diisi dengan benar ya.';
            } elseif ($form['id'] == '') {
                $this->model->tambah($form['nama'], $form['harga'], $form['satuan']);
                catatLog('Tambah layanan', $form['nama']);
                $this->pesan('sukses', 'Layanan baru berhasil ditambahin.');
                $this->pindahKe('layanan.php');
            } else {
                $this->model->ubah($form['id'], $form['nama'], $form['harga'], $form['satuan']);
                catatLog('Ubah layanan', $form['nama']);
                $this->pesan('sukses', 'Layanan berhasil diubah.');
                $this->pindahKe('layanan.php');
            }
        }

        // ----- DELETE -----
        if ($this->adaPost() && $this->post('aksi') == 'hapus') {
            $layanan = $this->model->cari($this->post('id'));
            if ($layanan) {
                $this->model->hapus($layanan['id']);
                catatLog('Hapus layanan', $layanan['nama']);
                $this->pesan('sukses', 'Layanan dihapus. Pesanan lama tetap nyimpen nama & harganya kok.');
            }
            $this->pindahKe('layanan.php');
        }

        // ----- mode edit: isi form pakai data yang dipilih -----
        if ($this->get('edit') != '' && $error == '') {
            $dipilih = $this->model->cari($this->get('edit'));
            if ($dipilih) {
                $form = $dipilih;
            }
        }

        $this->tampilkan('layanan/index', [
            'judulHalaman' => 'Layanan',
            'subJudul'     => 'Daftar layanan & harganya. Ini yang muncul di form pesanan pelanggan.',
            'error'        => $error,
            'form'         => $form,
            'modeEdit'     => $form['id'] != '',
            'daftar'       => $this->model->semua(),
        ]);
    }
}
