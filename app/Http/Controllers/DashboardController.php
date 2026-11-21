<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\Promo;
use App\Models\Ulasan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->isAdmin()) {
            return $this->dashboardStaff($request);
        }
        return $this->dashboardPelanggan();
    }

    private function dashboardStaff(Request $request)
    {
        $hariIni = date('Y-m-d');

        $totalPendapatan = 0;
        $pendapatanHariIni = 0;
        foreach (Pesanan::where('berat', '>', 0)->get() as $p) {
            $totalPendapatan += $p->total();
            if ($p->tanggal->format('Y-m-d') == $hariIni) {
                $pendapatanHariIni += $p->total();
            }
        }

        $periode = $request->periode == 30 ? 30 : 7;
        $grafik = $this->dataGrafik($periode);

        return view('dashboard.staff', [
            'jumlahSemua'       => Pesanan::count(),
            'jumlahHariIni'     => Pesanan::whereDate('tanggal', $hariIni)->count(),
            'jumlahBaru'        => Pesanan::where('status', 'Menunggu Jemput')->count(),
            'jumlahDiproses'    => Pesanan::whereIn('status', ['Diterima', 'Dicuci'])->count(),
            'jumlahSelesai'     => Pesanan::whereIn('status', ['Selesai', 'Diambil'])->count(),
            'jumlahPelanggan'   => User::where('role', 'customer')->count(),
            'totalPendapatan'   => $totalPendapatan,
            'pendapatanHariIni' => $pendapatanHariIni,
            'periode'           => $periode,
            'grafik'            => $grafik,
            'perluDijemput'     => Pesanan::with('user')->where('status', 'Menunggu Jemput')->orderBy('tanggal')->get(),
            'terbaru'           => Pesanan::with('user')->where('berat', '>', 0)->orderBy('tanggal', 'desc')->limit(8)->get(),
            'rataRating'        => round((float) Ulasan::avg('rating'), 1),
            'jumlahUlasan'      => Ulasan::count(),
            'statusProses'      => Pesanan::STATUS_PROSES,
        ]);
    }

    private function dataGrafik($periode)
    {
        $awal = date('Y-m-d', strtotime('-' . ($periode - 1) . ' days'));
        $pesananPeriode = Pesanan::whereDate('tanggal', '>=', $awal)->get();

        $hari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $data = [];
        for ($i = $periode - 1; $i >= 0; $i--) {
            $waktu = strtotime("-$i days");
            $tanggal = date('Y-m-d', $waktu);
            $data[$tanggal] = [
                'label'      => $periode == 7 ? $hari[date('w', $waktu)] : date('j', $waktu),
                'judul'      => tglPendek($tanggal),
                'jumlah'     => 0,
                'pendapatan' => 0,
                'hariIni'    => $i == 0,
            ];
        }

        foreach ($pesananPeriode as $p) {
            $tanggal = $p->tanggal->format('Y-m-d');
            if (isset($data[$tanggal])) {
                $data[$tanggal]['jumlah']++;
                $data[$tanggal]['pendapatan'] += $p->total();
            }
        }

        $maks = 1;
        foreach ($data as $d) {
            if ($d['jumlah'] > $maks) {
                $maks = $d['jumlah'];
            }
        }
        foreach ($data as $tanggal => $d) {
            $data[$tanggal]['tinggi'] = round($d['jumlah'] / $maks * 100);
        }

        return $data;
    }

    private function dashboardPelanggan()
    {
        $user = Auth::user();
        $pesananSaya = Pesanan::with('ulasan')
            ->where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->get();

        $aktif = null;
        $jumlahBerjalan = 0;
        $totalBelanja = 0;
        foreach ($pesananSaya as $p) {
            if (!$p->sudahSelesai()) {
                $jumlahBerjalan++;
                if ($aktif == null) {
                    $aktif = $p;
                }
            }
            $totalBelanja += $p->total();
        }

        return view('dashboard.pelanggan', [
            'pesananSaya'    => $pesananSaya,
            'aktif'          => $aktif,
            'jumlahBerjalan' => $jumlahBerjalan,
            'totalBelanja'   => $totalBelanja,
            'terakhir'       => $pesananSaya->take(4),
            'promoAktif'     => Promo::yangAktif(),
            'daftarStatus'   => Pesanan::DAFTAR_STATUS,
            'keterangan'     => [
                'Menunggu Jemput' => 'Kurir lagi otw',
                'Diterima'        => 'Sudah ditimbang',
                'Dicuci'          => 'Lagi dicuci',
                'Selesai'         => 'Siap diambil',
                'Diambil'         => 'Sudah di tanganmu',
            ],
        ]);
    }
}
