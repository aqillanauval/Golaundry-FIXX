<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
{
    public function index(Request $request)
    {
        return view('pesanan.index', [
            'dipilih'      => null,
            'promoDipilih' => $request->promo, 
            'layananList'  => Layanan::orderBy('id')->get(),
            'promoAktif'   => Promo::yangAktif(),
            'pesananSaya'  => $this->pesananSaya(),
        ]);
    }

    public function edit(Pesanan $pesanan)
    {
        $this->pastikanBisaDiubah($pesanan);

        return view('pesanan.index', [
            'dipilih'      => $pesanan,
            'promoDipilih' => $pesanan->punyaDiskon() ? 'lama' : '',
            'layananList'  => Layanan::orderBy('id')->get(),
            'promoAktif'   => Promo::yangAktif(),
            'pesananSaya'  => $this->pesananSaya(),
        ]);
    }

    public function store(Request $request)
    {
        $this->validasi($request);
        $layanan = Layanan::find($request->layanan_id);

        $promo = null;
        if ($request->filled('promo_id')) {
            $promo = Promo::cariAktif($request->promo_id);
            if (!$promo) {
                return back()->withErrors(['promo_id' => 'Promo tidak valid atau sudah habis masa berlakunya.'])->withInput();
            }
        }

        $user = Auth::user();
        Pesanan::create([
            'kode'           => Pesanan::buatKode(),
            'user_id'        => $user->id,
            'nama_pelanggan' => $user->nama,
            'sumber'         => 'aplikasi',
            'layanan_id'     => $layanan->id,
            'nama_layanan'   => $layanan->nama,   
            'harga'          => $layanan->harga,  
            'satuan'         => $layanan->satuan,
            'berat'          => 0,                
            'catatan'        => trim($request->catatan),
            'status'         => 'Menunggu Jemput',
            'promo_id'       => $promo ? $promo->id : null,
            'nama_promo'     => $promo ? $promo->judul : '',
            'diskon'         => $promo ? $promo->diskon : 0,
            'tanggal'        => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('sukses', 'Pesanan masuk! Kurir akan menjemput dan menimbang cucianmu.');
    }

    public function update(Request $request, Pesanan $pesanan)
    {
        $this->pastikanBisaDiubah($pesanan);
        $this->validasi($request);
        $layanan = Layanan::find($request->layanan_id);

        $pesanan->layanan_id = $layanan->id;
        $pesanan->nama_layanan = $layanan->nama;
        $pesanan->harga = $layanan->harga;
        $pesanan->satuan = $layanan->satuan;
        $pesanan->catatan = trim($request->catatan);

        if ($request->promo_id == '') {
            $pesanan->promo_id = null;
            $pesanan->nama_promo = '';
            $pesanan->diskon = 0;
        } elseif ($request->promo_id != 'lama') {
            $promo = Promo::cariAktif($request->promo_id);
            if (!$promo) {
                return back()->withErrors(['promo_id' => 'Promo tidak valid atau sudah habis masa berlakunya.'])->withInput();
            }
            $pesanan->promo_id = $promo->id;
            $pesanan->nama_promo = $promo->judul;
            $pesanan->diskon = $promo->diskon;
        }

        $pesanan->save();

        return redirect()->route('pesanan.index')->with('sukses', 'Pesanan berhasil diubah.');
    }

    public function destroy(Pesanan $pesanan)
    {
        $this->pastikanBisaDiubah($pesanan);
        $pesanan->delete();

        return redirect()->route('pesanan.index')->with('sukses', 'Pesanan berhasil dibatalkan.');
    }

    private function validasi(Request $request)
    {
        $request->validate([
            'layanan_id' => 'required|exists:layanan,id',
            'catatan'    => 'required|string|max:500',
            'promo_id'   => 'nullable',
        ], [
            'layanan_id.required' => 'Pilih layanannya dulu ya.',
            'catatan.required'    => 'Isi alamat jemput dulu ya, biar kurir bisa menemukanmu.',
        ]);
    }

    private function pastikanBisaDiubah(Pesanan $pesanan)
    {
        if ($pesanan->user_id != Auth::id()) {
            abort(403);
        }
        if (!$pesanan->menungguJemput()) {
            abort(redirect()->route('pesanan.index')
                ->with('error', 'Pesanan ini sudah dijemput, jadi tidak bisa diubah atau dibatalkan lagi.'));
        }
    }

    private function pesananSaya()
    {
        return Pesanan::where('user_id', Auth::id())
            ->orderBy('tanggal', 'desc')
            ->get();
    }
}
