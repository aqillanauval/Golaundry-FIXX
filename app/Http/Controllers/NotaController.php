<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Support\Facades\Auth;

class NotaController extends Controller
{
    public function show(Pesanan $pesanan)
    {
        $user = Auth::user();

        // Pelanggan tidak boleh membuka nota milik orang lain
        if (!$user->isAdmin() && $pesanan->user_id != $user->id) {
            abort(404);
        }

        return view('nota.show', [
            'pesanan'     => $pesanan,
            'linkKembali' => $user->isAdmin() ? route('transaksi.index') : route('riwayat.index'),
        ]);
    }
}
