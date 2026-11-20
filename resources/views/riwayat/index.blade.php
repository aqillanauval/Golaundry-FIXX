@extends('layouts.app')

@section('judul', 'Riwayat Pesanan')

@section('konten')
<div class="judul-halaman">
  <h1>Riwayat Pesanan</h1>
  <p>Semua pesanan laundry kamu. Pesanan yang sudah selesai bisa diberi ulasan.</p>
</div>

@if ($formUlasan)
  {{-- FORM ULASAN (tambah / ubah) --}}
  <section class="panel" id="ulasan">
    <div class="panel-kepala">
      <div>
        <h3>{{ $formUlasan->ulasan ? 'Ubah Ulasan' : 'Beri Ulasan' }}</h3>
        <p><span class="kode">{{ $formUlasan->kode }}</span> &middot; {{ $formUlasan->nama_layanan }} &middot; {{ tglPendek($formUlasan->tanggal) }}</p>
      </div>
      <a href="{{ route('riwayat.index') }}" class="btn btn-garis btn-kecil">Tutup</a>
    </div>

    <form method="POST" action="{{ route('riwayat.ulasan', $formUlasan) }}">
      @csrf
      @php $ratingLama = old('rating', $formUlasan->ulasan->rating ?? 5); @endphp
      <div class="form-grup">
        <span class="label">Rating</span>
        <div class="bintang-input">
          @for ($i = 5; $i >= 1; $i--)
            <input type="radio" id="bintang{{ $i }}" name="rating" value="{{ $i }}" {{ $ratingLama == $i ? 'checked' : '' }}>
            <label for="bintang{{ $i }}" title="{{ $i }} bintang">★</label>
          @endfor
        </div>
      </div>
      <div class="form-grup">
        <label for="komentar">Komentar</label>
        <textarea id="komentar" name="komentar" class="input @error('komentar') salah @enderror" maxlength="500"
                  placeholder="Ceritain pengalamanmu, misalnya soal wangi, kerapian, atau kurirnya" required>{{ old('komentar', $formUlasan->ulasan->komentar ?? '') }}</textarea>
      </div>
      <button type="submit" class="btn btn-utama"><i class="fa-solid fa-paper-plane"></i> Simpan Ulasan</button>
    </form>
  </section>
@endif

<section class="panel">
  <div class="filter-bar" style="margin-bottom:14px">
    <a href="{{ route('riwayat.index') }}" class="chip-filter {{ !$statusFilter ? 'aktif' : '' }}">Semua</a>
    @foreach (\App\Models\Pesanan::DAFTAR_STATUS as $s)
      <a href="{{ route('riwayat.index', ['status' => $s]) }}" class="chip-filter {{ $statusFilter == $s ? 'aktif' : '' }}">{{ $s }}</a>
    @endforeach
  </div>

  <div class="tabel-wrap">
    <table>
      <thead><tr><th>No. Nota</th><th>Layanan</th><th>Berat</th><th>Total</th><th>Status</th><th>Ulasan</th><th>Aksi</th></tr></thead>
      <tbody>
        @forelse ($pesananSaya as $p)
          <tr>
            <td><a href="{{ route('nota', $p) }}" class="kode">{{ $p->kode }}</a><br><span class="muted kecil nowrap">{{ tglPendek($p->tanggal, true) }}</span></td>
            <td>{{ $p->nama_layanan }}</td>
            <td class="nowrap">
              @if ($p->sudahDitimbang())
                {{ formatBerat($p->berat) }} {{ $p->satuan }}
              @else
                <span class="tag tunggu">Belum ditimbang</span>
              @endif
            </td>
            <td class="uang">
              {{ $p->sudahDitimbang() ? rupiah($p->total()) : '-' }}
              @if ($p->punyaDiskon())<br><span class="tag promo">-{{ $p->diskon }}%</span>@endif
            </td>
            <td>@include('partials.status', ['status' => $p->status])</td>
            <td>
              @if ($p->ulasan)
                <span class="bintang">
                  @for ($i = 1; $i <= 5; $i++)<span class="{{ $i <= $p->ulasan->rating ? '' : 'mati' }}">★</span>@endfor
                </span>
                <div class="muted kecil">{{ \Illuminate\Support\Str::limit($p->ulasan->komentar, 40) }}</div>
              @elseif ($p->sudahSelesai())
                <span class="muted kecil">Belum diulas</span>
              @else
                <span class="muted kecil">-</span>
              @endif
            </td>
            <td>
              <div class="aksi">
                <a href="{{ route('nota', $p) }}" class="btn btn-garis btn-kecil btn-ikon" title="Nota"><i class="fa-solid fa-receipt"></i></a>
                @if ($p->sudahSelesai())
                  <a href="{{ route('riwayat.index', ['ulas' => $p->kode]) }}#ulasan" class="btn btn-garis btn-kecil" title="Ulasan">
                    <i class="fa-solid fa-star"></i> {{ $p->ulasan ? 'Ubah' : 'Ulas' }}
                  </a>
                  @if ($p->ulasan)
                    <form method="POST" action="{{ route('riwayat.ulasan.hapus', $p) }}" class="inline" data-konfirmasi="Ulasan untuk pesanan {{ $p->kode }} akan dihapus.">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-hapus btn-kecil btn-ikon" title="Hapus ulasan"><i class="fa-solid fa-trash-can"></i></button>
                    </form>
                  @endif
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="kosong">{{ $statusFilter ? 'Tidak ada pesanan dengan status ini.' : 'Belum ada riwayat pesanan.' }}</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</section>
@endsection
