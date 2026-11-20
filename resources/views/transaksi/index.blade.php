@extends('layouts.app')

@section('judul', 'Transaksi')

@section('konten')
<div class="judul-halaman">
  <h1>Transaksi</h1>
  <p>Semua pesanan dari aplikasi dan kios. Klik no. nota untuk melihat atau mencetak nota.</p>
</div>

<div class="stat-grid tiga">
  <div class="stat peach"><h3>Semua Transaksi</h3><span class="ket">Aplikasi + kios</span><span class="angka">{{ $jumlahSemua }}</span></div>
  <div class="stat biru"><h3>Menunggu Jemput</h3><span class="ket">Belum ditimbang</span><span class="angka">{{ $jumlahMenunggu }}</span></div>
  <div class="stat mint"><h3>Total Pendapatan</h3><span class="ket">Semua yang sudah ditimbang</span><span class="angka uang">{{ rupiah($totalPendapatan) }}</span></div>
</div>

<div class="grid-form">
  {{-- FORM KIOS / EDIT --}}
  <section class="panel lengket" id="form">
    <div class="panel-kepala">
      <div>
        <h3>{{ $dipilih ? 'Edit Transaksi' : 'Catat Pelanggan Kios' }}</h3>
        <p>{{ $dipilih ? $dipilih->kode . ' · ' . $dipilih->namaPelanggan() : 'Untuk pelanggan yang datang langsung.' }}</p>
      </div>
      @if ($dipilih)<span class="form-mode"><i class="fa-solid fa-pen"></i> Mode edit</span>@endif
    </div>

    <form method="POST" action="{{ $dipilih ? route('transaksi.update', $dipilih) : route('transaksi.store') }}">
      @csrf
      @if ($dipilih) @method('PUT') @endif

      @if (!$dipilih)
        <div class="form-grup">
          <label for="nama_pelanggan">Nama pelanggan</label>
          <input type="text" id="nama_pelanggan" name="nama_pelanggan" class="input @error('nama_pelanggan') salah @enderror"
                 value="{{ old('nama_pelanggan') }}" maxlength="100" required>
        </div>
      @endif

      <div class="form-grup">
        <label for="layanan_id">Layanan</label>
        <select id="layanan_id" name="layanan_id" class="input @error('layanan_id') salah @enderror" required>
          <option value="">-- pilih layanan --</option>
          @foreach ($layananList as $l)
            <option value="{{ $l->id }}" data-harga="{{ $l->harga }}" data-satuan="{{ $l->satuan }}"
              {{ old('layanan_id', $dipilih->layanan_id ?? '') == $l->id ? 'selected' : '' }}>
              {{ $l->nama }} ({{ rupiah($l->harga) }}/{{ $l->satuan }})
            </option>
          @endforeach
        </select>
      </div>

      <div class="baris-form">
        <div class="form-grup">
          <label for="berat">Berat / jumlah</label>
          <input type="number" id="berat" name="berat" class="input @error('berat') salah @enderror" step="0.1" min="0.1" max="500"
                 value="{{ old('berat', ($dipilih && $dipilih->sudahDitimbang()) ? $dipilih->berat : '') }}" required>
        </div>
        <div class="form-grup">
          <label for="status">Status</label>
          <select id="status" name="status" class="input @error('status') salah @enderror">
            @foreach (\App\Models\Pesanan::STATUS_PROSES as $s)
              <option value="{{ $s }}" {{ old('status', $dipilih->status ?? 'Diterima') == $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
          </select>
        </div>
      </div>

      @if (!$dipilih)
        <div class="form-grup">
          <label for="promo_id">Promo</label>
          <select id="promo_id" name="promo_id" class="input @error('promo_id') salah @enderror">
            <option value="" data-diskon="0">Tanpa promo</option>
            @foreach ($promoAktif as $p)
              <option value="{{ $p->id }}" data-diskon="{{ $p->diskon }}" {{ old('promo_id') == $p->id ? 'selected' : '' }}>
                {{ $p->judul }} ({{ $p->diskon }}%)
              </option>
            @endforeach
          </select>
        </div>
      @endif

      <div class="form-grup">
        <label for="catatan">Catatan <span class="muted">(opsional)</span></label>
        <textarea id="catatan" name="catatan" class="input" maxlength="500" rows="2">{{ old('catatan', $dipilih->catatan ?? '') }}</textarea>
      </div>

      <div class="estimasi">
        <div>
          <small>Total bayar{{ ($dipilih && $dipilih->punyaDiskon()) ? ' (promo ' . $dipilih->diskon . '%)' : '' }}</small>
          <strong id="estimasi" data-diskon-tetap="{{ $dipilih->diskon ?? 0 }}">Rp 0</strong>
        </div>
        <i class="fa-solid fa-calculator" style="font-size:22px;color:var(--ungu)"></i>
      </div>

      <div class="aksi">
        <button type="submit" class="btn btn-utama"><i class="fa-solid fa-floppy-disk"></i> {{ $dipilih ? 'Simpan Perubahan' : 'Catat Transaksi' }}</button>
        @if ($dipilih)<a href="{{ route('transaksi.index') }}" class="btn btn-garis">Batal</a>@endif
      </div>
    </form>
  </section>

  {{-- DAFTAR TRANSAKSI --}}
  <section class="panel">
    <form method="GET" action="{{ route('transaksi.index') }}" class="filter-bar" style="margin-bottom:12px">
      <input type="search" name="cari" class="input" value="{{ $cari }}" placeholder="Cari no. nota, pelanggan, layanan...">
      <select name="status" class="input" style="flex:0 1 180px" onchange="this.form.submit()">
        <option value="">Semua status</option>
        @foreach (\App\Models\Pesanan::DAFTAR_STATUS as $s)
          <option value="{{ $s }}" {{ $statusFilter == $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
      </select>
      <button type="submit" class="btn btn-garis btn-kecil"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
      @if ($cari || $statusFilter)<a href="{{ route('transaksi.index') }}" class="btn btn-kecil btn-garis">Reset</a>@endif
    </form>

    <div class="tabel-wrap">
      <table>
        <thead><tr><th>No. Nota</th><th>Pelanggan</th><th>Layanan</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          @forelse ($daftar as $p)
            <tr>
              <td>
                <a href="{{ route('nota', $p) }}" class="kode">{{ $p->kode }}</a><br>
                <span class="muted kecil nowrap">{{ tglPendek($p->tanggal, true) }}</span>
              </td>
              <td>{{ $p->namaPelanggan() }}<br><span class="tag {{ $p->sumber }}">{{ $p->asal() }}</span></td>
              <td>
                {{ $p->nama_layanan }}<br>
                @if ($p->sudahDitimbang())
                  <span class="muted kecil">{{ formatBerat($p->berat) }} {{ $p->satuan }}</span>
                @else
                  <span class="tag tunggu">Belum ditimbang</span>
                @endif
              </td>
              <td class="uang">
                {{ $p->sudahDitimbang() ? rupiah($p->total()) : '-' }}
                @if ($p->punyaDiskon())<br><span class="tag promo">-{{ $p->diskon }}%</span>@endif
              </td>
              <td>
                @if ($p->sudahDitimbang())
                  <select class="select-status" data-url="{{ route('transaksi.status', $p) }}" aria-label="Status {{ $p->kode }}">
                    @foreach (\App\Models\Pesanan::STATUS_PROSES as $s)
                      <option value="{{ $s }}" {{ $p->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                  </select>
                @else
                  @include('partials.status', ['status' => $p->status])
                @endif
                @if ($p->ulasan)<br><span class="bintang kecil">★ {{ $p->ulasan->rating }}</span>@endif
              </td>
              <td>
                <div class="aksi">
                  <a href="{{ route('transaksi.edit', $p) }}#form" class="btn btn-garis btn-kecil btn-ikon" title="Edit"><i class="fa-solid fa-pen"></i></a>
                  <a href="{{ route('nota', $p) }}" class="btn btn-garis btn-kecil btn-ikon" title="Nota"><i class="fa-solid fa-receipt"></i></a>
                  @include('partials.hapus', ['url' => route('transaksi.destroy', $p), 'pesan' => 'Transaksi ' . $p->kode . ' akan dihapus permanen.'])
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="kosong">{{ $cari || $statusFilter ? 'Tidak ada transaksi yang cocok.' : 'Belum ada transaksi.' }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $daftar->links('partials.pagination') }}
  </section>
</div>
@endsection

@push('script')
  <script src="{{ asset('js/pesanan.js') }}"></script>
  <script src="{{ asset('js/dashboard-staff.js') }}"></script>
@endpush
