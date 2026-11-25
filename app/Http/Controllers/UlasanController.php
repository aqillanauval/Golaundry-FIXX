<?php

namespace App\Http\Controllers;

use App\Helpers\LogAktivitas;
use App\Models\Ulasan;
use Illuminate\Http\Request;

class UlasanController extends Controller
{
    public function index(Request $request)
    {
        $query = Ulasan::with('pesanan.user')->orderBy('created_at', 'desc');

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $perBintang = [];
        for ($i = 5; $i >= 1; $i--) {
            $perBintang[$i] = Ulasan::where('rating', $i)->count();
        }

        return view('ulasan.index', [
            'daftar'       => $query->get(),
            'ratingFilter' => $request->rating,
            'rataRata'     => round((float) Ulasan::avg('rating'), 1),
            'jumlah'       => Ulasan::count(),
            'perBintang'   => $perBintang,
        ]);
    }

    public function destroy(Ulasan $ulasan)
    {
        $ulasan->delete();
        LogAktivitas::catat('Hapus ulasan pelanggan', ($ulasan->pesanan->kode ?? '-') . ' - ' . $ulasan->rating . ' bintang');

        return redirect()->route('ulasan.index')->with('sukses', 'Ulasan berhasil dihapus.');
    }
}
