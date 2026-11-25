<?php

namespace App\Http\Controllers;

use App\Helpers\LogAktivitas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        return view('pelanggan.index', [
            'daftar'  => $this->ambilDaftar($request),
            'dipilih' => null,
            'cari'    => $request->cari,
        ]);
    }

    public function edit(Request $request, User $pelanggan)
    {
        $this->pastikanPelanggan($pelanggan);

        return view('pelanggan.index', [
            'daftar'  => $this->ambilDaftar($request),
            'dipilih' => $pelanggan,
            'cari'    => $request->cari,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge(['username' => normalUser($request->username)]);
        $request->validate([
            'nama'     => 'required|string|max:100',
            'username' => 'required|string|max:100|unique:users,username',
            'password' => 'required|string|min:6',
        ]);

        if (!usernameValid($request->username)) {
            return back()->withErrors(['username' => 'Isi pakai email yang valid atau nomor HP (9-15 angka).'])
                ->withInput($request->except('password'));
        }

        User::create([
            'nama'     => trim($request->nama),
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => 'customer',
        ]);
        LogAktivitas::catat('Tambah pelanggan', trim($request->nama) . ' (' . $request->username . ')');

        return redirect()->route('pelanggan.index')->with('sukses', 'Pelanggan baru berhasil ditambahkan.');
    }

    public function update(Request $request, User $pelanggan)
    {
        $this->pastikanPelanggan($pelanggan);

        $request->merge(['username' => normalUser($request->username)]);
        $request->validate([
            'nama'     => 'required|string|max:100',
            'username' => 'required|string|max:100|unique:users,username,' . $pelanggan->id,
            'password' => 'nullable|string|min:6', 
        ]);

        if (!usernameValid($request->username)) {
            return back()->withErrors(['username' => 'Isi pakai email yang valid atau nomor HP (9-15 angka).'])
                ->withInput($request->except('password'));
        }

        $pelanggan->nama = trim($request->nama);
        $pelanggan->username = $request->username;
        if ($request->filled('password')) {
            $pelanggan->password = Hash::make($request->password);
        }
        $pelanggan->save();
        LogAktivitas::catat('Ubah pelanggan', $pelanggan->nama . ($request->filled('password') ? ' + reset password' : ''));

        return redirect()->route('pelanggan.index')->with('sukses', 'Data pelanggan berhasil diubah.');
    }

    public function destroy(User $pelanggan)
    {
        $this->pastikanPelanggan($pelanggan);
        $pelanggan->hapusAkunPelanggan();
        LogAktivitas::catat('Hapus pelanggan', $pelanggan->nama . ' (' . $pelanggan->username . ')');

        return redirect()->route('pelanggan.index')
            ->with('sukses', 'Akun pelanggan dihapus. Riwayat transaksinya tetap disimpan.');
    }

    private function pastikanPelanggan(User $user)
    {
        if ($user->isAdmin()) {
            abort(404);
        }
    }

    private function ambilDaftar(Request $request)
    {
        $query = User::where('role', 'customer')->withCount('pesanan')->orderBy('nama');
        if ($request->filled('cari')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->cari . '%')
                  ->orWhere('username', 'like', '%' . $request->cari . '%');
            });
        }
        return $query->get();
    }
}
