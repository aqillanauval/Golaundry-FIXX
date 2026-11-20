@extends('layouts.auth')

@section('judul', 'Login')

@section('konten')
  <h1>Selamat datang 👋</h1>
  <p class="muted">Login dulu untuk pesan laundry atau kelola GOLaundry.</p>

  <form method="POST" action="{{ route('login.proses') }}">
    @csrf

    <div class="form-grup">
      <label for="username">Email atau No. HP</label>
      <input type="text" id="username" name="username" class="input @error('username') salah @enderror"
             value="{{ old('username', $usernameTersimpan) }}" placeholder="contoh: kamu@email.com" autofocus required>
    </div>

    <div class="form-grup">
      <label for="password">Password</label>
      <div class="input-password">
        <input type="password" id="password" name="password" class="input @error('username') salah @enderror"
               placeholder="Masukkan password" required>
        <button type="button" class="lihat-password" aria-label="Lihat password"><i class="fa-solid fa-eye"></i></button>
      </div>
    </div>

    <div class="form-grup">
      <label class="check">
        <input type="checkbox" name="ingat" {{ $usernameTersimpan ? 'checked' : '' }}>
        Ingat email/no. HP saya
      </label>
    </div>

    <button type="submit" class="btn btn-utama">Login <i class="fa-solid fa-arrow-right"></i></button>
  </form>

  <p class="auth-bawah">Belum punya akun? <a href="{{ route('register') }}">Registrasi di sini</a></p>

  <div class="akun-demo">
    <i class="fa-solid fa-circle-info"></i> Akun percobaan:
    staff <b>admin@golaundry.com</b> / <b>admin123</b>,
    pelanggan <b>pelanggan@golaundry.com</b> / <b>pelanggan123</b>
  </div>
@endsection
