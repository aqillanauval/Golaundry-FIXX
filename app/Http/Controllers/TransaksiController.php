<?php

namespace App\Http\Controllers;

use App\Helpers\LogAktivitas;
use App\Models\Karyawan;
use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        return view('transaksi.index', $this->dataHalaman($request, null));
    }

    public function edit(Request $request, Pesanan $pesanan)
    {
        return view('transaksi.index', $this->dataHalaman($request, $pesanan));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:100',
            'layanan_id'     => 'required|exists:layanan,id',
            'berat'          => 'required|numeric|min:0.1|max:500',
            'status'         => 'required|in:' . implode(',', Pesanan::STATUS_PROSES),
            'catatan'        => 'nullable|string|max:500',
            'promo_id'       => 'nullable',
        ]);

        $promo = null;
        if ($request->filled('promo_id')) {
            $promo = Promo::cariAktif($request->promo_id);
            if (!$promo) {
                return back()->withErrors(['promo_id' => 'Promo tidak valid atau sudah habis masa berlakunya.'])->withInput();
            }
        }

        $layanan = Layanan::find($request->layanan_id);
        $pesanan = Pesanan::create([
            'kode'           => Pesanan::buatKode(),
            'user_id'        => null, 
            'nama_pelanggan' => trim($request->nama_pelanggan),
            'sumber'         => 'kios',
            'layanan_id'     => $layanan->id,
            'nama_layanan'   => $layanan->nama,
            'harga'          => $layanan->harga,
            'satuan'         => $layanan->satuan,
            'berat'          => round($request->berat, 1),
            'catatan'        => trim((string) $request->catatan),
            'status'         => $request->status,
            'promo_id'       => $promo ? $promo->id : null,
            'nama_promo'     => $promo ? $promo->judul : '',
            'diskon'         => $promo ? $promo->diskon : 0,
            'tanggal'        => now(),
        ]);

        LogAktivitas::catat('Catat transaksi kios', $pesanan->kode . ' - ' . $pesanan->nama_pelanggan . ' - ' . rupiah($pesanan->total()));

        return redirect()->route('transaksi.index')->with('sukses', 'Transaksi pelanggan kios berhasil dicatat.');
    }

    public function update(Request $request, Pesanan $pesanan)
    {
        $request->validate([
            'layanan_id' => 'required|exists:layanan,id',
            'berat'      => 'required|numeric|min:0.1|max:500',
            'status'     => 'required|in:' . implode(',', Pesanan::STATUS_PROSES),
            'catatan'    => 'nullable|string|max:500',
        ]);

        $layanan = Layanan::find($request->layanan_id);
        $pesanan->layanan_id = $layanan->id;
        $pesanan->nama_layanan = $layanan->nama;
        $pesanan->harga = $layanan->harga;
        $pesanan->satuan = $layanan->satuan;
        $pesanan->berat = round($request->berat, 1);
        $pesanan->status = $request->status;
        $pesanan->catatan = trim((string) $request->catatan);
        $pesanan->save();
        LogAktivitas::catat('Ubah transaksi', $pesanan->kode . ' - ' . $pesanan->status . ' - ' . rupiah($pesanan->total()));

        return redirect()->route('transaksi.index')->with('sukses', 'Transaksi ' . $pesanan->kode . ' berhasil diubah.');
    }

    public function destroy(Pesanan $pesanan)
    {
        $pesanan->delete();
        LogAktivitas::catat('Hapus transaksi', $pesanan->kode . ' - ' . $pesanan->namaPelanggan());

        return redirect()->route('transaksi.index')->with('sukses', 'Transaksi berhasil dihapus.');
    }

    public function isiBerat(Request $request, Pesanan $pesanan)
    {
        $request->validate([
            'berat'    => 'required|numeric|min:0.1|max:500',
            // kurir wajib dipilih, dan harus karyawan yang jabatannya Kurir
            'kurir_id' => [
                'required',
                Rule::exists('karyawan', 'id')->where(function ($query) {
                    $query->whereRaw('LOWER(TRIM(jabatan)) = ?', ['kurir']);
                }),
            ],
        ], [
            'kurir_id.required' => 'Pilih kurir yang menjemput cucian ini.',
            'kurir_id.exists'   => 'Kurir yang dipilih tidak ditemukan.',
        ]);

        if (!$pesanan->menungguJemput()) {
            return back()->with('error', 'Berat pesanan ini sudah pernah diisi.');
        }

        $pesanan->berat = round($request->berat, 1);
        $pesanan->kurir_id = $request->kurir_id;
        $pesanan->status = 'Diterima';
        $pesanan->save();

        $kurir = Karyawan::find($pesanan->kurir_id);
        LogAktivitas::catat('Isi berat', $pesanan->kode . ' - ' . formatBerat($pesanan->berat) . ' ' . $pesanan->satuan . ' - kurir ' . $kurir->nama);

        return back()->with('sukses', 'Berat ' . formatBerat($pesanan->berat) . ' ' . $pesanan->satuan
            . ' untuk ' . $pesanan->namaPelanggan() . ' tersimpan (dijemput ' . $kurir->nama . '). Total bayar ' . rupiah($pesanan->total()) . '.');
    }

    public function ubahStatus(Request $request, Pesanan $pesanan)
    {
        if (!in_array($request->status, Pesanan::STATUS_PROSES)) {
            return response()->json(['sukses' => false, 'pesan' => 'Status tidak valid.']);
        }
        if (!$pesanan->sudahDitimbang()) {
            return response()->json(['sukses' => false, 'pesan' => 'Isi berat cuciannya dulu ya.']);
        }

        $pesanan->status = $request->status;
        $pesanan->save();
        LogAktivitas::catat('Ubah status', $pesanan->kode . ' jadi ' . $pesanan->status);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Status ' . $pesanan->kode . ' diganti jadi ' . $pesanan->status . '.',
        ]);
    }

    // Unduh daftar transaksi (sesuai filter yang sedang dipakai) sebagai file CSV untuk Excel
    public function export(Request $request)
    {
        $daftar = $this->queryDaftar($request)->with('kurir')->get();
        $namaFile = 'transaksi-golaundry-' . date('Y-m-d') . '.csv';

        LogAktivitas::catat('Export transaksi', $daftar->count() . ' baris');

        return response()->streamDownload(function () use ($daftar) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // BOM supaya huruf terbaca benar di Excel
            fputcsv($file, ['No. Nota', 'Tanggal', 'Pelanggan', 'Asal', 'Layanan', 'Harga', 'Satuan',
                'Berat/Jumlah', 'Subtotal', 'Promo', 'Diskon (%)', 'Potongan', 'Total', 'Status', 'Kurir'], ';');

            foreach ($daftar as $p) {
                fputcsv($file, [
                    $p->kode, $p->tanggal->format('Y-m-d H:i'), $p->namaPelanggan(), $p->asal(),
                    $p->nama_layanan, $p->harga, $p->satuan, $p->berat, $p->subtotal(),
                    $p->nama_promo, $p->diskon, $p->potongan(), $p->total(), $p->status,
                    $p->kurir->nama ?? '',
                ], ';');
            }
            fclose($file);
        }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // Query daftar transaksi + filter pencarian, dipakai halaman daftar dan export
    private function queryDaftar(Request $request)
    {
        $query = Pesanan::with('user', 'ulasan')->orderBy('tanggal', 'desc');

        if ($request->filled('cari')) {
            $cari = '%' . $request->cari . '%';
            $query->where(function ($q) use ($cari) {
                $q->where('kode', 'like', $cari)
                  ->orWhere('nama_pelanggan', 'like', $cari)
                  ->orWhere('nama_layanan', 'like', $cari);
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    private function dataHalaman(Request $request, $dipilih)
    {
        $query = $this->queryDaftar($request);

        $totalPendapatan = 0;
        foreach (Pesanan::where('berat', '>', 0)->get() as $p) {
            $totalPendapatan += $p->total();
        }

        return [
            'daftar'          => $query->paginate(12)->withQueryString(),
            'dipilih'         => $dipilih,
            'cari'            => $request->cari,
            'statusFilter'    => $request->status,
            'layananList'     => Layanan::orderBy('id')->get(),
            'promoAktif'      => Promo::yangAktif(),
            'totalPendapatan' => $totalPendapatan,
            'jumlahSemua'     => Pesanan::count(),
            'jumlahMenunggu'  => Pesanan::where('status', 'Menunggu Jemput')->count(),
        ];
    }
}
