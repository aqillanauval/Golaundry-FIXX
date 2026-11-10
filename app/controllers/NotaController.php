<?php
class NotaController extends Controller
{
    public function invoke()
    {
        $saya = $this->harusLogin();
        $pesananModel = new PesananModel();
        $kode = $this->get('id');

        if ($saya->isAdmin()) {
            $pesanan = $pesananModel->cariByKode($kode);
            $linkKembali = 'transaksi.php';
        } else {
            $pesanan = $pesananModel->cariMilikUser($kode, $saya->getId());
            $linkKembali = 'riwayat.php';
        }

        $this->tampilkan('nota/index', [
            'pesanan'     => $pesanan,
            'linkKembali' => $linkKembali,
        ]);
    }
}
