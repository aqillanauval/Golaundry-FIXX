<?php
// ============================================================
// NotaController.php  ->  nota pembayaran (bisa dicetak / disimpan PDF)
// Dibuka lewat link nota.php?id=KODE
// Pelanggan cuma bisa buka nota punya sendiri, admin bisa semua.
// ============================================================

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
