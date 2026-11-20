<?php

namespace App\Http\Controllers;

use App\Models\Ulasan;
use Illuminate\Http\Request;

// Staff: melihat semua ulasan pelanggan dan menghapus ulasan yang tidak pantas
class UlasanController extends Controller
{
    public function index(Request $request)
    {
        $query = Ulasan::with('pesanan.user')->orderBy('created_at', 'desc');

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Jumlah ulasan per bintang, untuk grafik kecil di atas
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

        return redirect()->route('ulasan.index')->with('sukses', 'Ulasan berhasil dihapus.');
    }
}
