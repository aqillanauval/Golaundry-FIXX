@extends('layouts.app')

@section('judul', $dipilih ? 'Edit Pesanan' : 'Buat Pesanan')

@section('konten')
<div class="judul-halaman">
  <h1>{{ $dipilih ? 'Edit Pesanan' : 'Buat Pesanan' }}</h1>
  <p>Pilih layanannya aja. Berat cucian ditimbang kurir waktu jemput.</p>
</div>

<div class="grid-form">
  <section class="panel lengket" id="form">
    <div class="panel-kepala">
      <div>
        <h3>{{ $dipilih ? 'Ubah ' . $dipilih->kode : 'Pesanan Baru' }}</h3>
        <p>{{ $dipilih ? 'Masih bisa diubah sebelum kurir menjemput.' : 'Kurir akan datang ke alamatmu.' }}</p>
      </div>
      @if ($dipilih)<span class="form-mode"><i class="fa-solid fa-pen"></i> Mode edit</span>@endif
    </div>

    <form method="POST" action="{{ $dipilih ? route('pesanan.update', $dipilih) : route('pesanan.store') }}">
      @csrf
      @if ($dipilih) @method('PUT') @endif

      <div class="form-grup">
        <span class="label">Jenis layanan</span>
        <div class="pilih-kartu">
          @foreach ($layananList as $l)
            <label>
              <input type="radio" name="layanan_id" value="{{ $l->id }}" data-harga="{{ $l->harga }}" data-satuan="{{ $l->satuan }}"
                {{ old('layanan_id', $dipilih->layanan_id ?? '') == $l->id ? 'checked' : '' }}>
              <span class="isi">
                <strong>{{ $l->nama }}</strong>
                <small>{{ rupiah($l->harga) }} / {{ $l->satuan }}</small>
              </span>
            </label>
          @endforeach
        </div>
      </div>

      <div class="form-grup">
        <label for="catatan">Alamat jemput</label>
        <textarea id="catatan" name="catatan" class="input @error('catatan') salah @enderror" maxlength="500"
                  placeholder="contoh: Kos Melati kamar 7, Jl. Imam Bonjol No. 12" required>{{ old('catatan', $dipilih->catatan ?? '') }}</textarea>
      </div>

      <div class="form-grup">
        <label for="promo_id">Promo</label>
        @php $promoTerpilih = old('promo_id', $promoDipilih); @endphp
        <select id="promo_id" name="promo_id" class="input @error('promo_id') salah @enderror">
          <option value="" data-diskon="0">Tanpa promo</option>
          @if ($dipilih && $dipilih->punyaDiskon())
            <option value="lama" data-diskon="{{ $dipilih->diskon }}" {{ $promoTerpilih == 'lama' ? 'selected' : '' }}>
              Tetap pakai: {{ $dipilih->nama_promo }} ({{ $dipilih->diskon }}%)
            </option>
          @endif
          @foreach ($promoAktif as $p)
            <option value="{{ $p->id }}" data-diskon="{{ $p->diskon }}" {{ (string) $promoTerpilih === (string) $p->id ? 'selected' : '' }}>
              {{ $p->judul }} ({{ $p->diskon }}%)
            </option>
          @endforeach
        </select>
      </div>

      <div class="estimasi">
        <div>
          <small>Perkiraan harga setelah promo</small>
          <strong id="estimasi">Pilih layanan dulu</strong>
        </div>
        <i class="fa-solid fa-scale-balanced" style="font-size:22px;color:var(--ungu)"></i>
      </div>

      <div class="aksi">
        <button type="submit" class="btn btn-utama"><i class="fa-solid fa-paper-plane"></i> {{ $dipilih ? 'Simpan Perubahan' : 'Pesan Sekarang' }}</button>
        @if ($dipilih)<a href="{{ route('pesanan.index') }}" class="btn btn-garis">Batal</a>@endif
      </div>
    </form>
  </section>

  <section class="panel">
    <div class="panel-kepala"><div><h3>Pesanan Saya</h3><p>Pesanan yang belum dijemput masih bisa diubah atau dibatalkan.</p></div></div>
    <div class="tabel-wrap">
      <table>
        <thead><tr><th>No. Nota</th><th>Layanan</th><th>Alamat</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          @forelse ($pesananSaya as $p)
            <tr>
              <td><span class="kode">{{ $p->kode }}</span><br><span class="muted kecil nowrap">{{ tglPendek($p->tanggal, true) }}</span></td>
              <td>{{ $p->nama_layanan }} @if ($p->punyaDiskon())<br><span class="tag promo">-{{ $p->diskon }}%</span>@endif</td>
              <td class="muted kecil">{{ \Illuminate\Support\Str::limit($p->catatan, 40) }}</td>
              <td>@include('partials.status', ['status' => $p->status])</td>
              <td>
                @if ($p->menungguJemput())
                  <div class="aksi">
                    <a href="{{ route('pesanan.edit', $p) }}#form" class="btn btn-garis btn-kecil btn-ikon" title="Edit"><i class="fa-solid fa-pen"></i></a>
                    @include('partials.hapus', ['url' => route('pesanan.destroy', $p), 'pesan' => 'Pesanan ' . $p->kode . ' akan dibatalkan.'])
                  </div>
                @else
                  <a href="{{ route('nota', $p) }}" class="btn btn-garis btn-kecil"><i class="fa-solid fa-receipt"></i> Nota</a>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="kosong">Belum ada pesanan. Yuk buat yang pertama!</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>
@endsection

@push('script')
  <script src="{{ asset('js/pesanan.js') }}"></script>
@endpush
