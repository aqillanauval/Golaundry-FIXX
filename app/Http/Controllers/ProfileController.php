<?php

namespace App\Http\Controllers;

use App\Helpers\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{

    public function index()
    {
        $user = Auth::user();

        return view('profil.index', [
            'user'          => $user,
            'jumlahPesanan' => $user->pesanan()->count(),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $request->merge(['username' => normalUser($request->username)]);

        $request->validate([
            'nama'          => 'required|string|max:100',
            'username'      => 'required|string|max:100|unique:users,username,' . $user->id,
            'password_baru' => 'nullable|string|min:6|confirmed',
            'password_lama' => 'nullable|required_with:password_baru|current_password',
        ]);

        if (!usernameValid($request->username)) {
            return back()
                ->withErrors(['username' => 'Isi pakai email yang valid atau nomor HP (9-15 angka).'])
                ->withInput();
        }

        $user->nama = trim($request->nama);
        $user->username = $request->username;
        if ($request->filled('password_baru')) {
            $user->password = Hash::make($request->password_baru);
        }
        $user->save();
        LogAktivitas::catat('Ubah profil', $request->filled('password_baru') ? 'Termasuk ganti password' : '');
        // Nama staff juga disimpan di data karyawan, jadi ikut diperbarui
        if ($user->karyawan) {
            $user->karyawan->update(['nama' => $user->nama]);
        }

        return redirect()->route('profil')->with('sukses', 'Profil berhasil disimpan.');
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return back()->with('error', 'Akun staff tidak bisa dihapus dari halaman profil.');
        }

        $request->validate(
            ['konfirmasi_password' => 'required|current_password'],
            ['konfirmasi_password.current_password' => 'Password salah, akun tidak jadi dihapus.']
        );
        LogAktivitas::catat('Hapus akun sendiri', $user->nama);
        $user->hapusAkunPelanggan();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('sukses', 'Akun kamu sudah dihapus. Terima kasih sudah memakai GOLaundry!');
    }
}
