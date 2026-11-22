<?php

namespace App\Http\Controllers;

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
        $data = $this->validasi($request);
        $data['foto'] = '';

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->simpanFoto($request);
        }

        Promo::create($data);

        return redirect()->route('promo.index')->with('sukses', 'Promo baru berhasil ditambahkan.');
    }

    public function update(Request $request, Promo $promo)
    {
        $data = $this->validasi($request);

        if ($request->hasFile('foto')) {
            $this->hapusFoto($promo->foto);           // foto lama dibuang
            $data['foto'] = $this->simpanFoto($request);
        }

        $promo->update($data);

        return redirect()->route('promo.index')->with('sukses', 'Promo berhasil diubah.');
    }

    public function destroy(Promo $promo)
    {
        $this->hapusFoto($promo->foto);
        $promo->delete(); 

        return redirect()->route('promo.index')->with('sukses', 'Promo berhasil dihapus.');
    }

    private function validasi(Request $request)
    {
        return $request->validate([
            'judul'          => 'required|string|max:100',
            'deskripsi'      => 'required|string|max:500',
            'diskon'         => 'required|integer|between:1,100',
            'berlaku_sampai' => 'required|date',
            'foto'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048', 
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
