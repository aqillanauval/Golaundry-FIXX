<?php
// ============================================================
// NotaController.php  -  Nota pembayaran (bisa dicetak / disimpan PDF)
// nota.php?id=KODE. Pelanggan hanya boleh membuka nota miliknya sendiri.
// ============================================================

class NotaController extends Controller
{
    public function invoke(): void
    {
        $saya = $this->wajibLogin();
        $model = new PesananModel();
        $kode = $this->get('id');

        $order = $saya->isAdmin()
            ? $model->cariByKode($kode)
            : $model->cariMilikUser($kode, $saya->getId());

        $this->view('nota/index', [
            'order'       => $order,
            'linkKembali' => $saya->isAdmin() ? 'transaksi.php' : 'riwayat.php',
        ]);
    }
}
