<?php
// ============================================================
// DashboardCustomerController.php
// Nampilin "tiket" pesanan yang lagi jalan, promo yang bisa dipakai,
// sama 3 transaksi terakhir pelanggan yang login.
// ============================================================

class DashboardCustomerController extends Controller
{
    public function invoke()
    {
        $saya = $this->harusLogin('customer');
        $pesananModel = new PesananModel();
        $promoModel = new PromoModel();

        $pesananSaya = $pesananModel->milikUser($saya->getId());

        // cari pesanan terbaru yang belum Selesai / Diambil
        $aktif = null;
        foreach ($pesananSaya as $p) {
            if (!$p->sudahSelesai()) {
                $aktif = $p;
                break;
            }
        }

        // posisi langkah sekarang (0 = Menunggu Jemput, 1 = Diterima, dst)
        $langkahSekarang = -1;
        if ($aktif) {
            $langkahSekarang = array_search($aktif->status, Pesanan::DAFTAR_STATUS);
        }

        $this->tampilkan('dashboard/customer', [
            'judulHalaman'    => 'Dashboard',
            'subJudul'        => 'Pantau cucianmu dari dijemput sampai beres.',
            'sapa'            => sapaan(),
            'aktif'           => $aktif,
            'langkahSekarang' => $langkahSekarang,
            'daftarStatus'    => Pesanan::DAFTAR_STATUS,
            'keterangan'      => [
                'Menunggu Jemput' => 'Kurir otw',
                'Diterima'        => 'Udah ditimbang',
                'Dicuci'          => 'Lagi dicuci',
                'Selesai'         => 'Siap diambil',
                'Diambil'         => 'Udah di tanganmu',
            ],
            'terakhir'        => array_slice($pesananSaya, 0, 3),
            'promoAktif'      => $promoModel->aktif(),
        ]);
    }
}
