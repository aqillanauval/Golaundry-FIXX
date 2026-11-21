<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function formLogin(Request $request)
    {
        $usernameTersimpan = $request->cookie('ingat_user', '');

        return view('auth.login', ['usernameTersimpan' => $usernameTersimpan]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:100',
            'password' => 'required|string',
        ]);

        $username = normalUser($request->username);

        if (Auth::attempt(['username' => $username, 'password' => $request->password])) {
            
            $request->session()->regenerate();

            if ($request->has('ingat')) {
                Cookie::queue('ingat_user', $username, 60 * 24 * 30); // 30 hari (dalam menit)
            } else {
                Cookie::queue(Cookie::forget('ingat_user'));
            }

            return redirect()->route('dashboard')
                ->with('sukses', 'Login berhasil. Selamat datang, ' . Auth::user()->nama . '!');
        }

        return back()
            ->withErrors(['username' => 'Email/No. HP atau password salah.'])
            ->onlyInput('username');
    }


    public function formRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->merge(['username' => normalUser($request->username)]);

        $request->validate([
            'nama'     => 'required|string|max:100',
            'username' => 'required|string|max:100|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!usernameValid($request->username)) {
            return back()
                ->withErrors(['username' => 'Isi pakai email yang valid atau nomor HP (9-15 angka).'])
                ->withInput($request->except('password', 'password_confirmation'));
        }

        User::create([
            'nama'     => trim($request->nama),
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => 'customer', // registrasi publik selalu jadi pelanggan
        ]);

        return redirect()->route('login')
            ->with('sukses', 'Akun berhasil dibuat! Silakan login.');
    }


    public function logout(Request $request)
    {

        Auth::logout();
        $request->session()->invalidate();      
        $request->session()->regenerateToken(); 

        return redirect()->route('login')
            ->with('sukses', 'Kamu sudah logout. Sampai jumpa lagi!');
    }
}
