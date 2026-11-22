@extends('layouts.app')

@section('judul', 'Karyawan')

@section('konten')
<div class="judul-halaman">
  <h1>Karyawan</h1>
  <p>Data karyawan yang bekerja di GOLaundry.</p>
</div>

<div class="grid-form">
  <section class="panel lengket" id="form">
    <div class="panel-kepala">
      <div><h3>{{ $dipilih ? 'Edit Karyawan' : 'Tambah Karyawan' }}</h3></div>
      @if ($dipilih)<span class="form-mode"><i class="fa-solid fa-pen"></i> Mode edit</span>@endif
    </div>

    <form method="POST" action="{{ $dipilih ? route('karyawan.update', $dipilih) : route('karyawan.store') }}">
      @csrf
      @if ($dipilih) @method('PUT') @endif

      <div class="form-grup">
        <label for="nama">Nama</label>
        <input type="text" id="nama" name="nama" class="input @error('nama') salah @enderror"
               value="{{ old('nama', $dipilih->nama ?? '') }}" maxlength="100" required>
      </div>
      <div class="form-grup">
        <label for="jabatan">Jabatan</label>
        <input type="text" id="jabatan" name="jabatan" list="saranJabatan" class="input @error('jabatan') salah @enderror"
               value="{{ old('jabatan', $dipilih->jabatan ?? '') }}" maxlength="50" placeholder="Pilih atau ketik sendiri" required>
        <datalist id="saranJabatan">
          @foreach (\App\Models\Karyawan::SARAN_JABATAN as $j)
            <option value="{{ $j }}">
          @endforeach
        </datalist>
      </div>
      <div class="form-grup">
        <label for="no_hp">No. HP</label>
        <input type="tel" id="no_hp" name="no_hp" class="input @error('no_hp') salah @enderror"
               value="{{ old('no_hp', $dipilih->no_hp ?? '') }}" maxlength="16" placeholder="08123456789" required>
        <small class="bantuan">Angka saja, 9-15 digit.</small>
      </div>

      <div class="aksi">
        <button type="submit" class="btn btn-utama"><i class="fa-solid fa-floppy-disk"></i> {{ $dipilih ? 'Simpan Perubahan' : 'Tambah Karyawan' }}</button>
        @if ($dipilih)<a href="{{ route('karyawan.index') }}" class="btn btn-garis">Batal</a>@endif
      </div>
    </form>
  </section>

  <section class="panel">
    <div class="panel-kepala">
      <div><h3>Daftar Karyawan</h3><p>{{ $daftar->count() }} orang</p></div>
    </div>
    <form method="GET" action="{{ route('karyawan.index') }}" class="filter-bar" style="margin-bottom:14px">
      <input type="search" name="cari" class="input" value="{{ $cari }}" placeholder="Cari nama atau jabatan...">
      <button type="submit" class="btn btn-garis btn-kecil"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
    </form>
    <div class="tabel-wrap">
      <table>
        <thead><tr><th>Nama</th><th>Jabatan</th><th>No. HP</th><th>Penjemputan</th><th>Aksi</th></tr></thead>
        <tbody>
          @forelse ($daftar as $k)
            <tr>
              <td><strong>{{ $k->nama }}</strong></td>
              <td>
                <span class="tag">{{ $k->jabatan }}</span>
                @if ($k->akun)
                  <span class="tag aplikasi" title="{{ $k->akun->username }}"><i class="fa-solid fa-user-shield"></i> Akun staff</span>
                @endif
              </td>
              <td>{{ $k->no_hp != '' ? $k->no_hp : '-' }}</td>
              <td>{{ $k->isKurir() ? $k->pesanan_dijemput_count . ' pesanan' : '-' }}</td>
              <td>
                <div class="aksi">
                  <a href="{{ route('karyawan.edit', $k) }}#form" class="btn btn-garis btn-kecil btn-ikon" title="Edit"><i class="fa-solid fa-pen"></i></a>
                  @include('partials.hapus', ['url' => route('karyawan.destroy', $k), 'pesan' => 'Data karyawan "' . $k->nama . '" akan dihapus.'])
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="kosong">{{ $cari ? 'Tidak ada karyawan yang cocok.' : 'Belum ada data karyawan.' }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>
@endsection
