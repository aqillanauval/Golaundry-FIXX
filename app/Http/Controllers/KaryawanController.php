<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        return view('karyawan.index', [
            'daftar'  => $this->ambilDaftar($request),
            'dipilih' => null,
            'cari'    => $request->cari,
        ]);
    }

    public function edit(Request $request, Karyawan $karyawan)
    {
        return view('karyawan.index', [
            'daftar'  => $this->ambilDaftar($request),
            'dipilih' => $karyawan,
            'cari'    => $request->cari,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        Karyawan::create($data);

        return redirect()->route('karyawan.index')->with('sukses', 'Karyawan baru berhasil ditambahkan.');
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $data = $this->validasi($request);
        $karyawan->update($data);

        // Kalau karyawan ini punya akun staff, nama di akunnya ikut disamakan
        if ($karyawan->akun) {
            $karyawan->akun->update(['nama' => $karyawan->nama]);
        }

        return redirect()->route('karyawan.index')->with('sukses', 'Data karyawan berhasil diubah.');
    }

    public function destroy(Karyawan $karyawan)
    {
        // Karyawan yang punya akun staff tidak boleh dihapus (foreign key RESTRICT)
        if ($karyawan->akun) {
            return redirect()->route('karyawan.index')
                ->with('error', $karyawan->nama . ' masih punya akun staff, jadi datanya tidak bisa dihapus.');
        }

        $karyawan->delete();

        return redirect()->route('karyawan.index')->with('sukses', 'Karyawan berhasil dihapus.');
    }

    // Pencarian pakai where + LIKE, nilainya tetap dikirim terpisah (prepared statement)
    private function ambilDaftar(Request $request)
    {
        $query = Karyawan::with('akun')->withCount('pesananDijemput')->orderBy('nama');
        if ($request->filled('cari')) {
            $query->where('nama', 'like', '%' . $request->cari . '%')
                  ->orWhere('jabatan', 'like', '%' . $request->cari . '%');
        }
        return $query->get();
    }

    private function validasi(Request $request)
    {
        return $request->validate([
            'nama'    => 'required|string|max:100',
            'jabatan' => 'required|string|max:50',
            'no_hp'   => ['required', 'regex:/^\+?[0-9]{9,15}$/'],
        ], [
            'no_hp.regex' => 'No. HP harus berupa angka 9-15 digit.',
        ]);
    }
}
