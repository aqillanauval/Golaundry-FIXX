<?php

namespace App\Http\Controllers;

use App\Helpers\LogAktivitas;
use App\Models\Layanan;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    
    public function index()
    {
        return view('layanan.index', [
            'daftar'  => Layanan::withCount('pesanan')->orderBy('id')->get(),
            'dipilih' => null, 
        ]);
    }

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
        $layanan = Layanan::create($data);
        LogAktivitas::catat('Tambah layanan', $layanan->nama . ' (' . rupiah($layanan->harga) . '/' . $layanan->satuan . ')');

        return redirect()->route('layanan.index')->with('sukses', 'Layanan baru berhasil ditambahkan.');
    }

    public function update(Request $request, Layanan $layanan)
    {
        $data = $this->validasi($request);
        $layanan->update($data);
        LogAktivitas::catat('Ubah layanan', $layanan->nama . ' (' . rupiah($layanan->harga) . '/' . $layanan->satuan . ')');

        return redirect()->route('layanan.index')->with('sukses', 'Layanan berhasil diubah.');
    }

    public function destroy(Layanan $layanan)
    {
        $layanan->delete();
        LogAktivitas::catat('Hapus layanan', $layanan->nama);

        return redirect()->route('layanan.index')
            ->with('sukses', 'Layanan dihapus. Pesanan lama tetap menyimpan nama dan harganya.');
    }

    private function validasi(Request $request)
    {
        return $request->validate([
            'nama'   => 'required|string|max:100',
            'harga'  => 'required|integer|min:1|max:10000000',
            'satuan' => 'required|in:' . implode(',', Layanan::DAFTAR_SATUAN),
        ]);
    }
}
