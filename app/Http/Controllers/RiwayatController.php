<?php

namespace App\Http\Controllers;

use App\Helpers\LogAktivitas;
use App\Models\Pesanan;
use App\Models\Ulasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $query = Pesanan::with('ulasan')
            ->where('user_id', Auth::id())
            ->orderBy('tanggal', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $formUlasan = null;
        if ($request->filled('ulas')) {
            $formUlasan = Pesanan::with('ulasan')
                ->where('kode', $request->ulas)
                ->where('user_id', Auth::id())
                ->first();
            if ($formUlasan && !$formUlasan->sudahSelesai()) {
                $formUlasan = null;
            }
        }

        return view('riwayat.index', [
            'pesananSaya'  => $query->get(),
            'statusFilter' => $request->status,
            'formUlasan'   => $formUlasan,
        ]);
    }

    public function simpanUlasan(Request $request, Pesanan $pesanan)
    {
        $this->pastikanMilikSendiri($pesanan);

        if (!$pesanan->sudahSelesai()) {
            return back()->with('error', 'Ulasan baru bisa diberikan setelah cucian selesai.');
        }

        $request->validate([
            'rating'   => 'required|integer|between:1,5',
            'komentar' => 'required|string|max:500',
        ], [
            'rating.required' => 'Pilih bintang 1 sampai 5 ya.',
        ]);

        $sudahAda = $pesanan->ulasan != null;

        Ulasan::updateOrCreate(
            ['pesanan_id' => $pesanan->id],
            ['rating' => $request->rating, 'komentar' => trim($request->komentar)]
        );

        LogAktivitas::catat($sudahAda ? 'Ubah ulasan' : 'Beri ulasan', $pesanan->kode . ' - ' . $request->rating . ' bintang');

        return redirect()->route('riwayat.index')
            ->with('sukses', $sudahAda ? 'Ulasan berhasil diubah.' : 'Terima kasih! Ulasanmu sudah disimpan.');
    }

    public function hapusUlasan(Pesanan $pesanan)
    {
        $this->pastikanMilikSendiri($pesanan);

        if ($pesanan->ulasan) {
            $pesanan->ulasan->delete();
            LogAktivitas::catat('Hapus ulasan', $pesanan->kode);
        }

        return redirect()->route('riwayat.index')->with('sukses', 'Ulasan berhasil dihapus.');
    }

    private function pastikanMilikSendiri(Pesanan $pesanan)
    {
        if ($pesanan->user_id != Auth::id()) {
            abort(403);
        }
    }
}
