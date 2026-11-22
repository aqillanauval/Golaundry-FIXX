@extends('layouts.app')

@section('judul', 'Profil Saya')

@section('konten')
<div class="judul-halaman">
  <h1>Profil Saya</h1>
  <p>Lihat dan atur data akunmu.</p>
</div>

<div class="grid-form">
  {{-- LIHAT DATA --}}
  <section class="panel">
    <div class="profil-kepala">
      <span class="avatar besar {{ $user->isAdmin() ? 'ungu' : '' }}">{{ inisial($user->nama) }}</span>
      <div>
        <h2>{{ $user->nama }}</h2>
        <span class="tag {{ $user->isAdmin() ? '' : 'aplikasi' }}">{{ $user->namaRole() }}</span>
      </div>
    </div>
    <dl class="data-list">
      <div><dt>Nama</dt><dd>{{ $user->nama }}</dd></div>
      <div><dt>Email / No. HP</dt><dd>{{ $user->username }}</dd></div>
      <div><dt>Role</dt><dd>{{ $user->namaRole() }}</dd></div>
      @if ($user->isAdmin() && $user->karyawan)
        <div><dt>Jabatan</dt><dd>{{ $user->karyawan->jabatan }}</dd></div>
      @endif
      <div><dt>Terdaftar sejak</dt><dd>{{ tglPendek($user->created_at) }}</dd></div>
      @if (!$user->isAdmin())
        <div><dt>Jumlah pesanan</dt><dd>{{ $jumlahPesanan }}</dd></div>
      @endif
    </dl>
  </section>

  <div class="kolom">
    {{-- UBAH DATA --}}
    <section class="panel">
      <div class="panel-kepala"><div><h3>Ubah Data</h3><p>Kosongkan bagian password kalau tidak ingin menggantinya.</p></div></div>

      <form method="POST" action="{{ route('profil.update') }}">
        @csrf
        @method('PUT')

        <div class="baris-form">
          <div class="form-grup">
            <label for="nama">Nama</label>
            <input type="text" id="nama" name="nama" class="input @error('nama') salah @enderror"
                   value="{{ old('nama', $user->nama) }}" maxlength="100" required>
          </div>
          <div class="form-grup">
            <label for="username">Email / No. HP</label>
            <input type="text" id="username" name="username" class="input @error('username') salah @enderror"
                   value="{{ old('username', $user->username) }}" maxlength="100" required>
          </div>
        </div>

        <div class="baris-form">
          <div class="form-grup">
            <label for="password_baru">Password baru</label>
            <div class="input-password">
              <input type="password" id="password_baru" name="password_baru" class="input @error('password_baru') salah @enderror"
                     minlength="6" placeholder="Minimal 6 karakter" autocomplete="new-password">
              <button type="button" class="lihat-password" aria-label="Lihat password"><i class="fa-solid fa-eye"></i></button>
            </div>
          </div>
          <div class="form-grup">
            <label for="password_baru_confirmation">Ulangi password baru</label>
            <input type="password" id="password_baru_confirmation" name="password_baru_confirmation" class="input" minlength="6" autocomplete="new-password">
          </div>
        </div>

        <div class="form-grup">
          <label for="password_lama">Password lama</label>
          <input type="password" id="password_lama" name="password_lama" class="input @error('password_lama') salah @enderror"
                 placeholder="Wajib diisi kalau mengganti password" autocomplete="current-password">
        </div>

        <button type="submit" class="btn btn-utama"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
      </form>
    </section>

    {{-- HAPUS AKUN --}}
    <section class="panel zona-bahaya">
      <div class="panel-kepala">
        <div>
          <h3>Hapus Akun</h3>
          @if ($user->isAdmin())
            <p>Akun staff tidak bisa dihapus dari halaman ini supaya aplikasi selalu punya pengelola.</p>
          @else
            <p>Akunmu akan dihapus permanen. Pesanan yang belum dijemput ikut dibatalkan, tapi riwayat transaksi yang sudah terjadi tetap disimpan GOLaundry.</p>
          @endif
        </div>
      </div>

      @if (!$user->isAdmin())
        <form method="POST" action="{{ route('profil.destroy') }}" data-konfirmasi="Akunmu akan dihapus permanen dan kamu akan langsung logout.">
          @csrf
          @method('DELETE')
          <div class="form-grup">
            <label for="konfirmasi_password">Masukkan password untuk konfirmasi</label>
            <input type="password" id="konfirmasi_password" name="konfirmasi_password"
                   class="input @error('konfirmasi_password') salah @enderror" required>
          </div>
          <button type="submit" class="btn btn-hapus"><i class="fa-solid fa-user-xmark"></i> Hapus Akun Saya</button>
        </form>
      @endif
    </section>
  </div>
</div>
@endsection
