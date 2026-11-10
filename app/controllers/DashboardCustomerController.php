<?php
class DashboardCustomerController extends Controller
{
    public function invoke()
    {
        $saya = $this->harusLogin('customer');
        $pesananModel = new PesananModel();
        $promoModel = new PromoModel();

        $pesananSaya = $pesananModel->milikUser($saya->getId());

        $aktif = null;
        foreach ($pesananSaya as $p) {
            if (!$p->sudahSelesai()) {
                $aktif = $p;
                break;
            }
        }

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
