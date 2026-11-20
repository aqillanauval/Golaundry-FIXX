@extends('layouts.app')

@section('judul', 'Ulasan')

@section('konten')
<div class="judul-halaman">
  <h1>Ulasan Pelanggan</h1>
  <p>Apa kata pelanggan tentang layanan GOLaundry.</p>
</div>

<div class="grid-form">
  <section class="panel lengket">
    <div class="panel-kepala"><div><h3>Ringkasan</h3></div></div>
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
      <span style="font-size:40px;font-weight:800;color:var(--tinta)">{{ $rataRata }}</span>
      <div>
        <span class="bintang">
          @for ($i = 1; $i <= 5; $i++)<span class="{{ $i <= round($rataRata) ? '' : 'mati' }}">★</span>@endfor
        </span>
        <div class="muted kecil">dari {{ $jumlah }} ulasan</div>
      </div>
    </div>
    @foreach ($perBintang as $bintang => $total)
      <div class="rating-bar">
        <span class="nowrap">{{ $bintang }} ★</span>
        <div class="jalur"><div class="isi" style="width: {{ $jumlah > 0 ? round($total / $jumlah * 100) : 0 }}%"></div></div>
        <span class="muted">{{ $total }}</span>
      </div>
    @endforeach
  </section>

  <section class="panel">
    <div class="filter-bar" style="margin-bottom:14px">
      <a href="{{ route('ulasan.index') }}" class="chip-filter {{ !$ratingFilter ? 'aktif' : '' }}">Semua</a>
      @for ($i = 5; $i >= 1; $i--)
        <a href="{{ route('ulasan.index', ['rating' => $i]) }}" class="chip-filter {{ $ratingFilter == $i ? 'aktif' : '' }}">{{ $i }} ★</a>
      @endfor
    </div>

    <div class="item-list">
      @forelse ($daftar as $u)
        <div class="item">
          <div class="item-kepala">
            <span class="avatar" style="width:34px;height:34px;font-size:13px">{{ inisial($u->pesanan ? $u->pesanan->namaPelanggan() : '?') }}</span>
            <div>
              <strong>{{ $u->pesanan ? $u->pesanan->namaPelanggan() : 'Pesanan dihapus' }}</strong>
              <div class="muted kecil">
                @if ($u->pesanan)
                  <a href="{{ route('nota', $u->pesanan) }}" class="kode">{{ $u->pesanan->kode }}</a> &middot; {{ $u->pesanan->nama_layanan }} &middot;
                @endif
                {{ tglPendek($u->created_at, true) }}
              </div>
            </div>
            <span class="bintang" style="margin-left:auto">
              @for ($i = 1; $i <= 5; $i++)<span class="{{ $i <= $u->rating ? '' : 'mati' }}">★</span>@endfor
            </span>
          </div>
          {{-- {{ }} meng-escape isi komentar, jadi script dari pelanggan tampil sebagai teks biasa --}}
          <p>{{ $u->komentar }}</p>
          <div class="baris-bawah">
            @include('partials.hapus', ['url' => route('ulasan.destroy', $u), 'pesan' => 'Ulasan ini akan dihapus. Lakukan hanya kalau isinya tidak pantas.', 'teks' => 'Hapus'])
          </div>
        </div>
      @empty
        <div class="kosong-besar"><i class="fa-regular fa-star"></i>Belum ada ulasan{{ $ratingFilter ? ' dengan rating ini' : '' }}.</div>
      @endforelse
    </div>
  </section>
</div>
@endsection
