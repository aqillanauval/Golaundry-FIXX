<?php
// ============================================================
// DashboardCustomerController.php
// "Tiket" pesanan aktif, promo yang bisa dipakai, dan 3 transaksi terakhir.
// ============================================================

class DashboardCustomerController extends Controller
{
    public function invoke(): void
    {
        $saya = $this->wajibLogin('customer');
        $pesananSaya = (new PesananModel())->milikUser($saya->getId());

        // pesanan terbaru yang masih berjalan (belum Selesai / Diambil)
        $aktif = null;
        foreach ($pesananSaya as $o) {
            if (!$o->sudahSelesai()) {
                $aktif = $o;
                break;
            }
        }

        $this->view('dashboard/customer', [
            'judulHalaman'    => 'Dashboard',
            'subJudul'        => 'Pantau cucianmu dari jemput sampai selesai.',
            'sapa'            => sapaan(),
            'aktif'           => $aktif,
            'langkahSekarang' => $aktif ? array_search($aktif->status, Pesanan::DAFTAR_STATUS, true) : -1,
            'daftarStatus'    => Pesanan::DAFTAR_STATUS,
            'keterangan'      => [
                'Menunggu Jemput' => 'Kurir segera datang',
                'Diterima'        => 'Sudah ditimbang',
                'Dicuci'          => 'Sedang dicuci',
                'Selesai'         => 'Siap diambil',
                'Diambil'         => 'Sudah di tanganmu',
            ],
            'terakhir'        => array_slice($pesananSaya, 0, 3),
            'promoAktif'      => (new PromoModel())->aktif(),
        ]);
    }
}
