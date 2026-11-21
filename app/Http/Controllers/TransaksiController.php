<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\Promo;
use Illuminate\Http\Request;

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
        Pesanan::create([
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

        return redirect()->route('transaksi.index')->with('sukses', 'Transaksi ' . $pesanan->kode . ' berhasil diubah.');
    }

    public function destroy(Pesanan $pesanan)
    {
        $pesanan->delete(); 

        return redirect()->route('transaksi.index')->with('sukses', 'Transaksi berhasil dihapus.');
    }

    public function isiBerat(Request $request, Pesanan $pesanan)
    {
        $request->validate(['berat' => 'required|numeric|min:0.1|max:500']);

        if (!$pesanan->menungguJemput()) {
            return back()->with('error', 'Berat pesanan ini sudah pernah diisi.');
        }

        $pesanan->berat = round($request->berat, 1);
        $pesanan->status = 'Diterima';
        $pesanan->save();

        return back()->with('sukses', 'Berat ' . formatBerat($pesanan->berat) . ' ' . $pesanan->satuan
            . ' untuk ' . $pesanan->namaPelanggan() . ' tersimpan. Total bayar ' . rupiah($pesanan->total()) . '.');
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

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Status ' . $pesanan->kode . ' diganti jadi ' . $pesanan->status . '.',
        ]);
    }

    private function dataHalaman(Request $request, $dipilih)
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
