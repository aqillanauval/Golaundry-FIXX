@extends('layouts.app')

@section('judul', 'Pelanggan')

@section('konten')
<div class="judul-halaman">
  <h1>Pelanggan</h1>
  <p>Akun pelanggan yang terdaftar di GOLaundry.</p>
</div>

<div class="grid-form">
  <section class="panel lengket" id="form">
    <div class="panel-kepala">
      <div>
        <h3>{{ $dipilih ? 'Edit Pelanggan' : 'Tambah Pelanggan' }}</h3>
        <p>{{ $dipilih ? 'Kosongkan password kalau tidak diganti.' : 'Buatkan akun untuk pelanggan.' }}</p>
      </div>
      @if ($dipilih)<span class="form-mode"><i class="fa-solid fa-pen"></i> Mode edit</span>@endif
    </div>

    <form method="POST" action="{{ $dipilih ? route('pelanggan.update', $dipilih) : route('pelanggan.store') }}">
      @csrf
      @if ($dipilih) @method('PUT') @endif

      <div class="form-grup">
        <label for="nama">Nama</label>
        <input type="text" id="nama" name="nama" class="input @error('nama') salah @enderror"
               value="{{ old('nama', $dipilih->nama ?? '') }}" maxlength="100" required>
      </div>
      <div class="form-grup">
        <label for="username">Email / No. HP</label>
        <input type="text" id="username" name="username" class="input @error('username') salah @enderror"
               value="{{ old('username', $dipilih->username ?? '') }}" maxlength="100" required>
      </div>
      <div class="form-grup">
        <label for="password">{{ $dipilih ? 'Password baru (opsional)' : 'Password' }}</label>
        <input type="password" id="password" name="password" class="input @error('password') salah @enderror"
               minlength="6" placeholder="Minimal 6 karakter" autocomplete="new-password" {{ $dipilih ? '' : 'required' }}>
      </div>

      <div class="aksi">
        <button type="submit" class="btn btn-utama"><i class="fa-solid fa-floppy-disk"></i> {{ $dipilih ? 'Simpan Perubahan' : 'Tambah Pelanggan' }}</button>
        @if ($dipilih)<a href="{{ route('pelanggan.index') }}" class="btn btn-garis">Batal</a>@endif
      </div>
    </form>
  </section>

  <section class="panel">
    <div class="panel-kepala"><div><h3>Daftar Pelanggan</h3><p>{{ $daftar->count() }} akun</p></div></div>
    <form method="GET" action="{{ route('pelanggan.index') }}" class="filter-bar" style="margin-bottom:14px">
      <input type="search" name="cari" class="input" value="{{ $cari }}" placeholder="Cari nama, email, atau no. HP...">
      <button type="submit" class="btn btn-garis btn-kecil"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
    </form>
    <div class="tabel-wrap">
      <table>
        <thead><tr><th>Pelanggan</th><th>Email / No. HP</th><th>Pesanan</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
        <tbody>
          @forelse ($daftar as $u)
            <tr>
              <td>
                <div class="item-kepala" style="margin:0">
                  <span class="avatar" style="width:32px;height:32px;font-size:13px">{{ inisial($u->nama) }}</span>
                  <strong>{{ $u->nama }}</strong>
                </div>
              </td>
              <td>{{ $u->username }}</td>
              <td><span class="tag">{{ $u->pesanan_count }}</span></td>
              <td class="muted kecil nowrap">{{ tglPendek($u->created_at) }}</td>
              <td>
                <div class="aksi">
                  <a href="{{ route('pelanggan.edit', $u) }}#form" class="btn btn-garis btn-kecil btn-ikon" title="Edit"><i class="fa-solid fa-pen"></i></a>
                  @include('partials.hapus', [
                    'url' => route('pelanggan.destroy', $u),
                    'pesan' => 'Akun "' . $u->nama . '" akan dihapus. Riwayat transaksinya tetap disimpan.',
                  ])
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="kosong">{{ $cari ? 'Tidak ada pelanggan yang cocok.' : 'Belum ada pelanggan.' }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>
@endsection
