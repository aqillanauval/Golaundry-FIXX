@extends('layouts.app')

@section('judul', 'Dashboard')

@section('konten')
<div class="grid-dashboard">
  <div class="kolom">

    <section class="panel">
      <div class="sapa-panel">
        <div>
          <h1>Halo, {{ auth()->user()->nama }} 👋</h1>
          <p>{{ sapaan() }}! Pantau cucianmu dari dijemput sampai beres.</p>
        </div>
        <a href="{{ route('pesanan.index') }}" class="btn btn-utama"><i class="fa-solid fa-plus"></i> Buat Pesanan</a>
      </div>

      <div class="stat-grid tiga">
        <div class="stat peach">
          <h3>Total Pesanan</h3>
          <span class="ket">Sejak daftar</span>
          <span class="angka">{{ $pesananSaya->count() }}</span>
          <a href="{{ route('riwayat.index') }}" class="panah" aria-label="Riwayat"><i class="fa-solid fa-arrow-right panah-miring"></i></a>
        </div>
        <div class="stat biru">
          <h3>Sedang Berjalan</h3>
          <span class="ket">Belum selesai</span>
          <span class="angka">{{ $jumlahBerjalan }}</span>
          <a href="{{ route('pesanan.index') }}" class="panah" aria-label="Pesanan saya"><i class="fa-solid fa-arrow-right panah-miring"></i></a>
        </div>
        <div class="stat lavender">
          <h3>Total Belanja</h3>
          <span class="ket">Pesanan yang sudah ditimbang</span>
          <span class="angka uang">{{ rupiah($totalBelanja) }}</span>
          <a href="{{ route('riwayat.index') }}" class="panah" aria-label="Riwayat"><i class="fa-solid fa-arrow-right panah-miring"></i></a>
        </div>
      </div>
    </section>

    {{-- Status laundry terbaru --}}
    <section class="panel">
      <div class="panel-kepala">
        <div>
          <h3>Status Laundry Terbaru</h3>
          <p>
            @if ($aktif)
              <span class="kode">{{ $aktif->kode }}</span> &middot; {{ $aktif->nama_layanan }} &middot; dipesan {{ tglPendek($aktif->tanggal, true) }}
            @else
              Tidak ada cucian yang sedang diproses.
            @endif
          </p>
        </div>
        @if ($aktif)
          @include('partials.status', ['status' => $aktif->status])
        @endif
      </div>

      @if ($aktif)
        <div class="tracker">
          @foreach ($daftarStatus as $i => $s)
            @php
              $kelas = '';
              if ($i < $aktif->langkahStatus()) $kelas = 'lewat';
              if ($i == $aktif->langkahStatus()) $kelas = 'sekarang';
              $ikon = ['fa-truck', 'fa-scale-balanced', 'fa-soap', 'fa-shirt', 'fa-house-circle-check'][$i];
            @endphp
            <div class="tracker-langkah {{ $kelas }}">
              <div class="bulatan"><i class="fa-solid {{ $ikon }}"></i></div>
              <strong>{{ $s }}</strong>
              <small>{{ $keterangan[$s] }}</small>
            </div>
          @endforeach
        </div>

        <div class="estimasi" style="margin:16px 0 0">
          <div>
            <small>Total bayar</small>
            @if ($aktif->sudahDitimbang())
              <strong>{{ rupiah($aktif->total()) }}</strong>
              <span class="muted kecil">({{ formatBerat($aktif->berat) }} {{ $aktif->satuan }})</span>
            @else
              <strong style="font-size:15px">Menunggu ditimbang kurir</strong>
            @endif
          </div>
          @if ($aktif->menungguJemput())
            <a href="{{ route('pesanan.edit', $aktif) }}" class="btn btn-garis btn-kecil">Ubah pesanan</a>
          @else
            <a href="{{ route('nota', $aktif) }}" class="btn btn-garis btn-kecil"><i class="fa-solid fa-receipt"></i> Nota</a>
          @endif
        </div>
      @else
        <div class="kosong-besar">
          <i class="fa-solid fa-basket-shopping"></i>
          Cucian lagi numpuk? Yuk pesan, kurir kami yang jemput.
        </div>
      @endif
    </section>

    {{-- Riwayat singkat --}}
    <section class="panel">
      <div class="panel-kepala">
        <div><h3>Riwayat Pesanan</h3><p>4 pesanan terakhir</p></div>
        <a href="{{ route('riwayat.index') }}" class="btn btn-garis btn-kecil">Lihat semua</a>
      </div>
      <div class="tabel-wrap">
        <table>
          <thead><tr><th>No. Nota</th><th>Layanan</th><th>Tanggal</th><th>Total</th><th>Status</th></tr></thead>
          <tbody>
            @forelse ($terakhir as $p)
              <tr>
                <td><a href="{{ route('nota', $p) }}" class="kode">{{ $p->kode }}</a></td>
                <td>{{ $p->nama_layanan }}</td>
                <td class="muted kecil nowrap">{{ tglPendek($p->tanggal) }}</td>
                <td class="uang">{{ $p->sudahDitimbang() ? rupiah($p->total()) : '-' }}</td>
                <td>@include('partials.status', ['status' => $p->status])</td>
              </tr>
            @empty
              <tr><td colspan="5" class="kosong">Belum ada pesanan.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>

  {{-- Kolom kanan: promo --}}
  <div class="kolom">
    @php $promoUtama = $promoAktif->first(); @endphp
    @if ($promoUtama)
      <a href="{{ route('pesanan.index', ['promo' => $promoUtama->id]) }}" class="banner-promo"
         @if ($promoUtama->adaFoto()) style="background-image: url('{{ $promoUtama->urlFoto() }}')" @endif>
        <span class="tag"><i class="fa-solid fa-tag"></i> Diskon {{ $promoUtama->diskon }}%</span>
        <h3>{{ $promoUtama->judul }}</h3>
        <p>Berlaku sampai {{ tglPendek($promoUtama->berlaku_sampai) }}</p>
        <span class="panah-sudut"><i class="fa-solid fa-arrow-right panah-miring"></i></span>
      </a>
    @endif

    <section class="panel">
      <div class="panel-kepala"><div><h3>Promo Tersedia</h3><p>Klik untuk langsung dipakai</p></div></div>
      <div class="item-list">
        @forelse ($promoAktif as $promo)
          <div class="item">
            <div class="item-kepala">
              <span class="bulat"><i class="fa-solid fa-percent"></i></span>
              <strong>{{ $promo->judul }}</strong>
              <span class="tag promo" style="margin-left:auto">{{ $promo->diskon }}%</span>
            </div>
            <p>{{ $promo->deskripsi }}</p>
            <div class="baris-bawah">
              <span class="muted kecil">s.d. {{ tglPendek($promo->berlaku_sampai) }}</span>
              <a href="{{ route('pesanan.index', ['promo' => $promo->id]) }}" class="btn btn-garis btn-kecil" style="margin-left:auto">Pakai</a>
            </div>
          </div>
        @empty
          <p class="muted">Belum ada promo saat ini.</p>
        @endforelse
      </div>
    </section>

    <div class="cta">
      <div>
        <h3>Butuh dijemput?</h3>
        <p>Isi alamat kos, kurir datang.</p>
      </div>
      <a href="{{ route('pesanan.index') }}" class="btn btn-kecil">Pesan</a>
    </div>
  </div>
</div>
@endsection
