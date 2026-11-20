@extends('layouts.app')

@section('judul', 'Layanan')

@section('konten')
<div class="judul-halaman">
  <h1>Layanan</h1>
  <p>Jenis layanan dan harganya. Daftar ini yang muncul di form pesanan pelanggan.</p>
</div>

<div class="grid-form">
  {{-- FORM TAMBAH / EDIT --}}
  <section class="panel lengket" id="form">
    <div class="panel-kepala">
      <div>
        <h3>{{ $dipilih ? 'Edit Layanan' : 'Tambah Layanan' }}</h3>
        <p>{{ $dipilih ? 'Ubah data lalu simpan.' : 'Isi data layanan baru.' }}</p>
      </div>
      @if ($dipilih)<span class="form-mode"><i class="fa-solid fa-pen"></i> Mode edit</span>@endif
    </div>

    <form method="POST" action="{{ $dipilih ? route('layanan.update', $dipilih) : route('layanan.store') }}">
      @csrf
      @if ($dipilih) @method('PUT') @endif

      <div class="form-grup">
        <label for="nama">Nama layanan</label>
        <input type="text" id="nama" name="nama" class="input @error('nama') salah @enderror"
               value="{{ old('nama', $dipilih->nama ?? '') }}" maxlength="100" placeholder="contoh: Cuci Setrika" required>
      </div>
      <div class="baris-form">
        <div class="form-grup">
          <label for="harga">Harga (Rp)</label>
          <input type="number" id="harga" name="harga" class="input @error('harga') salah @enderror"
                 value="{{ old('harga', $dipilih->harga ?? '') }}" min="1" placeholder="10000" required>
        </div>
        <div class="form-grup">
          <label for="satuan">Satuan</label>
          <select id="satuan" name="satuan" class="input @error('satuan') salah @enderror" required>
            @foreach (\App\Models\Layanan::DAFTAR_SATUAN as $s)
              <option value="{{ $s }}" {{ old('satuan', $dipilih->satuan ?? 'kg') == $s ? 'selected' : '' }}>per {{ $s }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="aksi">
        <button type="submit" class="btn btn-utama"><i class="fa-solid fa-floppy-disk"></i> {{ $dipilih ? 'Simpan Perubahan' : 'Tambah Layanan' }}</button>
        @if ($dipilih)<a href="{{ route('layanan.index') }}" class="btn btn-garis">Batal</a>@endif
      </div>
    </form>
  </section>

  {{-- DAFTAR --}}
  <section class="panel">
    <div class="panel-kepala"><div><h3>Daftar Layanan</h3><p>{{ $daftar->count() }} layanan</p></div></div>
    <div class="tabel-wrap">
      <table>
        <thead><tr><th>#</th><th>Nama</th><th>Harga</th><th>Dipakai</th><th>Aksi</th></tr></thead>
        <tbody>
          @forelse ($daftar as $i => $l)
            <tr>
              <td class="muted">{{ $i + 1 }}</td>
              <td><strong>{{ $l->nama }}</strong></td>
              <td class="uang">{{ rupiah($l->harga) }} <span class="muted kecil">/ {{ $l->satuan }}</span></td>
              <td><span class="tag">{{ $l->pesanan_count }} pesanan</span></td>
              <td>
                <div class="aksi">
                  <a href="{{ route('layanan.edit', $l) }}#form" class="btn btn-garis btn-kecil btn-ikon" title="Edit"><i class="fa-solid fa-pen"></i></a>
                  @include('partials.hapus', [
                    'url' => route('layanan.destroy', $l),
                    'pesan' => 'Layanan "' . $l->nama . '" akan dihapus. Pesanan lama tetap menyimpan nama dan harganya.',
                  ])
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="kosong">Belum ada layanan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>
@endsection
