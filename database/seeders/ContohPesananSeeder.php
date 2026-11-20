<?php

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContohPesananSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggan = User::where('role', 'customer')->first();
        $daftarLayanan = Layanan::all();
        $namaKios = ['Rina', 'Bagus', 'Dewi', 'Fajar', 'Sinta', 'Yoga'];

        if ($daftarLayanan->count() == 0) {
            return;
        }

        for ($hari = 13; $hari >= 0; $hari--) {
            $jumlah = rand(1, 4);
            for ($i = 0; $i < $jumlah; $i++) {
                $layanan = $daftarLayanan->random();
                $dariAplikasi = $pelanggan && rand(0, 1) == 1;

                if ($hari == 0) {
                    $status = 'Dicuci';
                } elseif ($hari < 3) {
                    $status = 'Selesai';
                } else {
                    $status = 'Diambil';
                }

                Pesanan::create([
                    'kode'           => Pesanan::buatKode() . $i,
                    'user_id'        => $dariAplikasi ? $pelanggan->id : null,
                    'nama_pelanggan' => $dariAplikasi ? $pelanggan->nama : $namaKios[array_rand($namaKios)],
                    'sumber'         => $dariAplikasi ? 'aplikasi' : 'kios',
                    'layanan_id'     => $layanan->id,
                    'nama_layanan'   => $layanan->nama,
                    'harga'          => $layanan->harga,
                    'satuan'         => $layanan->satuan,
                    'berat'          => rand(10, 60) / 10,
                    'catatan'        => $dariAplikasi ? 'Kos Melati No. 7' : '',
                    'status'         => $status,
                    'diskon'         => 0,
                    'tanggal'        => date('Y-m-d H:i:s', strtotime("-$hari days") - rand(0, 30000)),
                ]);
            }
        }
    }
}
