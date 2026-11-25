<?php

namespace App\Http\Controllers;

use App\Helpers\LogAktivitas;
use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index()
    {
        return view('promo.index', [
            'daftar'  => Promo::withCount('pesanan')->orderBy('id', 'desc')->get(),
            'dipilih' => null,
        ]);
    }

    public function edit(Promo $promo)
    {
        return view('promo.index', [
            'daftar'  => Promo::withCount('pesanan')->orderBy('id', 'desc')->get(),
            'dipilih' => $promo,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request, true);
        $data['foto'] = '';

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->simpanFoto($request);
        }

        $promo = Promo::create($data);
        LogAktivitas::catat('Tambah promo', $promo->judul . ' (' . $promo->diskon . '%)');

        return redirect()->route('promo.index')->with('sukses', 'Promo baru berhasil ditambahkan.');
    }

    public function update(Request $request, Promo $promo)
    {
        $data = $this->validasi($request);

        if ($request->hasFile('foto')) {
            $this->hapusFoto($promo->foto);           
            $data['foto'] = $this->simpanFoto($request);
        }

        $promo->update($data);
        LogAktivitas::catat('Ubah promo', $promo->judul . ' (' . $promo->diskon . '%)');

        return redirect()->route('promo.index')->with('sukses', 'Promo berhasil diubah.');
    }

    public function destroy(Promo $promo)
    {
        $this->hapusFoto($promo->foto);
        $promo->delete();
        LogAktivitas::catat('Hapus promo', $promo->judul);

        return redirect()->route('promo.index')->with('sukses', 'Promo berhasil dihapus.');
    }

    // $promoBaru = true: promo baru tidak boleh langsung kedaluwarsa (tanggal di masa lalu)
    private function validasi(Request $request, $promoBaru = false)
    {
        return $request->validate([
            'judul'          => 'required|string|max:100',
            'deskripsi'      => 'required|string|max:500',
            'diskon'         => 'required|integer|between:1,100',
            'berlaku_sampai' => $promoBaru ? 'required|date|after_or_equal:today' : 'required|date',
            'foto'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'berlaku_sampai.after_or_equal' => 'Tanggal berlaku promo baru tidak boleh sebelum hari ini.',
        ]);
    }

    private function simpanFoto(Request $request)
    {
        $file = $request->file('foto');
        $namaBaru = uniqid('promo_') . '.' . strtolower($file->getClientOriginalExtension());
        $file->move(public_path('uploads/promo'), $namaBaru);
        return $namaBaru;
    }

    private function hapusFoto($namaFile)
    {
        $namaFile = basename((string) $namaFile); 
        $lokasi = public_path('uploads/promo/' . $namaFile);
        if ($namaFile != '' && file_exists($lokasi)) {
            unlink($lokasi);
        }
    }
}
