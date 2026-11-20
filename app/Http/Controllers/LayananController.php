<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    // Halaman daftar layanan + form tambah
    public function index()
    {
        return view('layanan.index', [
            'daftar'  => Layanan::withCount('pesanan')->orderBy('id')->get(),
            'dipilih' => null, // null = form dalam mode tambah
        ]);
    }

    // Halaman yang sama, tapi form terisi data yang mau diedit
    public function edit(Layanan $layanan)
    {
        return view('layanan.index', [
            'daftar'  => Layanan::withCount('pesanan')->orderBy('id')->get(),
            'dipilih' => $layanan,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        Layanan::create($data);

        return redirect()->route('layanan.index')->with('sukses', 'Layanan baru berhasil ditambahkan.');
    }

    public function update(Request $request, Layanan $layanan)
    {
        $data = $this->validasi($request);
        $layanan->update($data);

        return redirect()->route('layanan.index')->with('sukses', 'Layanan berhasil diubah.');
    }

    public function destroy(Layanan $layanan)
    {
        $layanan->delete();

        return redirect()->route('layanan.index')
            ->with('sukses', 'Layanan dihapus. Pesanan lama tetap menyimpan nama dan harganya.');
    }

    // Aturan validasi dipakai bersama oleh store() dan update()
    private function validasi(Request $request)
    {
        return $request->validate([
            'nama'   => 'required|string|max:100',
            'harga'  => 'required|integer|min:1|max:10000000',
            'satuan' => 'required|in:' . implode(',', Layanan::DAFTAR_SATUAN),
        ]);
    }
}
