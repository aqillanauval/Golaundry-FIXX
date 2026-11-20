@extends('layouts.auth')

@section('judul', 'Registrasi')

@section('konten')
  <h1>Buat akun baru</h1>
  <p class="muted">Gratis, cuma butuh satu menit.</p>

  <form method="POST" action="{{ route('register.proses') }}">
    @csrf

    <div class="form-grup">
      <label for="nama">Nama lengkap</label>
      <input type="text" id="nama" name="nama" class="input @error('nama') salah @enderror"
             value="{{ old('nama') }}" maxlength="100" placeholder="Nama kamu" required>
    </div>

    <div class="form-grup">
      <label for="username">Email atau No. HP</label>
      <input type="text" id="username" name="username" class="input @error('username') salah @enderror"
             value="{{ old('username') }}" maxlength="100" placeholder="kamu@email.com / 08123456789" required>
      <small class="bantuan">Dipakai untuk login nanti.</small>
    </div>

    <div class="baris-form">
      <div class="form-grup">
        <label for="password">Password</label>
        <div class="input-password">
          <input type="password" id="password" name="password" class="input @error('password') salah @enderror"
                 minlength="6" placeholder="Minimal 6 karakter" required>
          <button type="button" class="lihat-password" aria-label="Lihat password"><i class="fa-solid fa-eye"></i></button>
        </div>
      </div>
      <div class="form-grup">
        <label for="password_confirmation">Ulangi password</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               class="input @error('password') salah @enderror" minlength="6" placeholder="Ketik ulang" required>
      </div>
    </div>

    <button type="submit" class="btn btn-utama">Daftar sekarang</button>
  </form>

  <p class="auth-bawah">Sudah punya akun? <a href="{{ route('login') }}">Login</a></p>
@endsection
