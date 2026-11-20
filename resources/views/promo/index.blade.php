@extends('layouts.app')

@section('judul', 'Promo')

@section('konten')
<div class="judul-halaman">
  <h1>Promo</h1>
  <p>Promo aktif langsung bisa dipilih pelanggan saat memesan.</p>
</div>

<div class="grid-form">
  <section class="panel lengket" id="form">
    <div class="panel-kepala">
      <div><h3>{{ $dipilih ? 'Edit Promo' : 'Tambah Promo' }}</h3></div>
      @if ($dipilih)<span class="form-mode"><i class="fa-solid fa-pen"></i> Mode edit</span>@endif
    </div>

    {{-- enctype wajib ada supaya file foto ikut terkirim --}}
    <form method="POST" action="{{ $dipilih ? route('promo.update', $dipilih) : route('promo.store') }}" enctype="multipart/form-data">
      @csrf
      @if ($dipilih) @method('PUT') @endif

      <div class="form-grup">
        <label for="judul">Judul promo</label>
        <input type="text" id="judul" name="judul" class="input @error('judul') salah @enderror"
               value="{{ old('judul', $dipilih->judul ?? '') }}" maxlength="100" required>
      </div>
      <div class="form-grup">
        <label for="deskripsi">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" class="input @error('deskripsi') salah @enderror" maxlength="500" required>{{ old('deskripsi', $dipilih->deskripsi ?? '') }}</textarea>
      </div>
      <div class="baris-form">
        <div class="form-grup">
          <label for="diskon">Diskon (%)</label>
          <input type="number" id="diskon" name="diskon" class="input @error('diskon') salah @enderror"
                 value="{{ old('diskon', $dipilih->diskon ?? '') }}" min="1" max="100" required>
        </div>
        <div class="form-grup">
          <label for="berlaku_sampai">Berlaku sampai</label>
          <input type="date" id="berlaku_sampai" name="berlaku_sampai" class="input @error('berlaku_sampai') salah @enderror"
                 value="{{ old('berlaku_sampai', $dipilih ? $dipilih->berlaku_sampai->format('Y-m-d') : '') }}" required>
        </div>
      </div>
      <div class="form-grup">
        <label for="foto">Foto promo {{ $dipilih ? '(kosongkan kalau tidak diganti)' : '(opsional)' }}</label>
        <input type="file" id="foto" name="foto" class="input @error('foto') salah @enderror" accept=".jpg,.jpeg,.png">
        <small class="bantuan">JPG atau PNG, maksimal 2 MB.</small>
        @if ($dipilih && $dipilih->adaFoto())
          <img src="{{ $dipilih->urlFoto() }}" alt="Foto promo saat ini" style="margin-top:10px;width:100%;max-height:140px;object-fit:cover;border-radius:12px">
        @endif
      </div>

      <div class="aksi">
        <button type="submit" class="btn btn-utama"><i class="fa-solid fa-floppy-disk"></i> {{ $dipilih ? 'Simpan Perubahan' : 'Tambah Promo' }}</button>
        @if ($dipilih)<a href="{{ route('promo.index') }}" class="btn btn-garis">Batal</a>@endif
      </div>
    </form>
  </section>

  <section class="panel">
    <div class="panel-kepala"><div><h3>Daftar Promo</h3><p>{{ $daftar->count() }} promo</p></div></div>
    <div class="promo-grid">
      @forelse ($daftar as $p)
        <div class="promo-kartu">
          <div class="promo-foto">
            @if ($p->adaFoto())
              <img src="{{ $p->urlFoto() }}" alt="Foto {{ $p->judul }}">
            @else
              <i class="fa-solid fa-gift"></i>
            @endif
            <span class="persen">-{{ $p->diskon }}%</span>
          </div>
          <div class="promo-isi">
            <strong>{{ $p->judul }}</strong>
            <p>{{ $p->deskripsi }}</p>
            <div class="aksi" style="margin-top:6px">
              @if ($p->masihBerlaku())
                <span class="tag promo">s.d. {{ tglPendek($p->berlaku_sampai) }}</span>
              @else
                <span class="tag habis">Berakhir {{ tglPendek($p->berlaku_sampai) }}</span>
              @endif
              <span class="tag">{{ $p->pesanan_count }}× dipakai</span>
            </div>
            <div class="aksi" style="margin-top:10px">
              <a href="{{ route('promo.edit', $p) }}#form" class="btn btn-garis btn-kecil"><i class="fa-solid fa-pen"></i> Edit</a>
              @include('partials.hapus', ['url' => route('promo.destroy', $p), 'pesan' => 'Promo "' . $p->judul . '" beserta fotonya akan dihapus.', 'teks' => 'Hapus'])
            </div>
          </div>
        </div>
      @empty
        <p class="muted">Belum ada promo.</p>
      @endforelse
    </div>
  </section>
</div>
@endsection
