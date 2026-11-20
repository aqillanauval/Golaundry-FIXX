@extends('layouts.app')

@section('judul', 'Dashboard')

@section('konten')
<div class="grid-dashboard">

  {{-- ============ KOLOM KIRI ============ --}}
  <div class="kolom">

    <section class="panel">
      <div class="sapa-panel">
        <div>
          <h1>{{ sapaan() }}, {{ auth()->user()->nama }}</h1>
          <p>Ringkasan operasional GOLaundry &middot; {{ tanggalIndo() }}</p>
        </div>
        <div class="pil-info">
          <div class="pil gelap">
            <span class="ikon"><i class="fa-solid fa-wallet"></i></span>
            <span><strong>{{ rupiah($pendapatanHariIni) }}</strong><small>pendapatan hari ini</small></span>
          </div>
          <div class="pil terang">
            <span class="ikon"><i class="fa-solid fa-basket-shopping"></i></span>
            <span><strong>{{ $jumlahHariIni }} pesanan</strong><small>masuk hari ini</small></span>
          </div>
        </div>
      </div>

      <div class="stat-grid">
        <div class="stat peach">
          <h3>Total Pesanan</h3>
          <span class="ket">Semua transaksi</span>
          <span class="angka">{{ $jumlahSemua }}</span>
          <a href="{{ route('transaksi.index') }}" class="panah" aria-label="Lihat transaksi"><i class="fa-solid fa-arrow-right panah-miring"></i></a>
        </div>
        <div class="stat biru">
          <h3>Pesanan Baru</h3>
          <span class="ket">Menunggu dijemput</span>
          <span class="angka">{{ $jumlahBaru }}</span>
          <a href="#jemput" class="panah" aria-label="Lihat pesanan baru"><i class="fa-solid fa-arrow-right panah-miring"></i></a>
        </div>
        <div class="stat lavender">
          <h3>Diproses</h3>
          <span class="ket">Diterima &amp; dicuci</span>
          <span class="angka">{{ $jumlahDiproses }}</span>
          <a href="{{ route('transaksi.index', ['status' => 'Dicuci']) }}" class="panah" aria-label="Lihat yang diproses"><i class="fa-solid fa-arrow-right panah-miring"></i></a>
        </div>
        <div class="stat mint">
          <h3>Pendapatan</h3>
          <span class="ket">Total keseluruhan</span>
          <span class="angka uang">{{ rupiah($totalPendapatan) }}</span>
          <a href="{{ route('transaksi.index') }}" class="panah" aria-label="Lihat transaksi"><i class="fa-solid fa-arrow-right panah-miring"></i></a>
        </div>
      </div>
    </section>

    {{-- Grafik pesanan per hari --}}
    <section class="panel">
      <div class="panel-kepala">
        <div>
          <h3>Statistik Pesanan</h3>
          <p>Jumlah pesanan per hari, {{ $periode }} hari terakhir</p>
        </div>
        <div class="pilih-periode">
          <a href="{{ route('dashboard', ['periode' => 7]) }}" class="{{ $periode == 7 ? 'aktif' : '' }}">7 hari</a>
          <a href="{{ route('dashboard', ['periode' => 30]) }}" class="{{ $periode == 30 ? 'aktif' : '' }}">30 hari</a>
        </div>
      </div>

      @php
        $jumlahPeriode = 0;
        $pendapatanPeriode = 0;
        foreach ($grafik as $g) {
            $jumlahPeriode += $g['jumlah'];
            $pendapatanPeriode += $g['pendapatan'];
        }
      @endphp

      <div class="grafik-wrap">
        <div class="ringkas-grafik">
          <span class="angka">{{ $jumlahPeriode }}</span>
          <small>pesanan dalam {{ $periode }} hari</small>
          <div class="pil-kecil"><span class="tag promo">{{ rupiah($pendapatanPeriode) }}</span></div>
          <div class="pil-kecil"><span class="tag">{{ $jumlahSelesai }} pesanan selesai</span></div>
        </div>

        <div class="grafik {{ $periode == 30 ? 'rapat' : '' }}">
          @foreach ($grafik as $g)
            <div class="batang-kolom">
              <div class="batang-area">
                <div class="batang {{ $g['hariIni'] ? 'hari-ini' : '' }} {{ $g['jumlah'] == 0 ? 'kosong' : '' }}"
                     style="height: {{ max($g['tinggi'], 6) }}%">
                  <span class="tooltip">{{ $g['judul'] }}: {{ $g['jumlah'] }} pesanan</span>
                </div>
              </div>
              <span class="batang-label">{{ $g['label'] }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </section>

    {{-- Pesanan terbaru --}}
    <section class="panel">
      <div class="panel-kepala">
        <div>
          <h3>Pesanan Terbaru</h3>
          <p>Ganti status lewat dropdown, langsung tersimpan tanpa reload.</p>
        </div>
        <a href="{{ route('transaksi.index') }}" class="btn btn-garis btn-kecil">Lihat semua</a>
      </div>

      <div class="tabel-wrap">
        <table>
          <thead>
            <tr><th>No. Nota</th><th>Pelanggan</th><th>Layanan</th><th>Total</th><th>Tanggal</th><th>Status</th></tr>
          </thead>
          <tbody>
            @forelse ($terbaru as $p)
              <tr>
                <td><a href="{{ route('nota', $p) }}" class="kode">{{ $p->kode }}</a></td>
                <td>{{ $p->namaPelanggan() }} <br><span class="tag {{ $p->sumber }}">{{ $p->asal() }}</span></td>
                <td>{{ $p->nama_layanan }}<br><span class="muted kecil">{{ formatBerat($p->berat) }} {{ $p->satuan }}</span></td>
                <td class="uang">{{ rupiah($p->total()) }}</td>
                <td class="muted kecil nowrap">{{ tglPendek($p->tanggal, true) }}</td>
                <td>
                  <select class="select-status" data-url="{{ route('transaksi.status', $p) }}" aria-label="Status {{ $p->kode }}">
                    @foreach ($statusProses as $s)
                      <option value="{{ $s }}" {{ $p->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                  </select>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="kosong">Belum ada pesanan yang ditimbang.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>

  {{-- ============ KOLOM KANAN ============ --}}
  <div class="kolom">
    <a href="{{ route('transaksi.index') }}#form" class="banner-promo">
      <span class="tag"><i class="fa-solid fa-store"></i> Kios</span>
      <h3>Ada pelanggan datang langsung?</h3>
      <p>Catat transaksinya di menu Transaksi.</p>
      <span class="panah-sudut"><i class="fa-solid fa-arrow-right panah-miring"></i></span>
    </a>

    <section class="panel" id="jemput">
      <div class="panel-kepala">
        <div>
          <h3>Perlu Dijemput <span class="tag">{{ $perluDijemput->count() }}</span></h3>
          <p>Setelah ditimbang, isi beratnya di sini.</p>
        </div>
      </div>

      <div class="item-list">
        @forelse ($perluDijemput as $p)
          <div class="item">
            <div class="item-kepala">
              <span class="bulat oranye"><i class="fa-solid fa-truck"></i></span>
              <div>
                <strong>{{ $p->namaPelanggan() }}</strong>
                <div class="muted kecil">{{ $p->nama_layanan }} &middot; {{ rupiah($p->harga) }}/{{ $p->satuan }}</div>
              </div>
            </div>
            <p><i class="fa-solid fa-location-dot"></i> {{ $p->catatan }}</p>
            @if ($p->punyaDiskon())<span class="tag promo">Promo {{ $p->diskon }}%</span>@endif

            <form method="POST" action="{{ route('transaksi.berat', $p) }}" class="jemput-form">
              @csrf
              <div class="input-satuan">
                <label for="berat-{{ $p->kode }}" class="sr-saja">Berat</label>
                <input type="number" id="berat-{{ $p->kode }}" name="berat" min="0.1" max="500" step="0.1" placeholder="0,0" required>
                <span>{{ $p->satuan }}</span>
              </div>
              <button type="submit" class="btn btn-utama btn-kecil">Simpan</button>
            </form>
          </div>
        @empty
          <div class="kosong-besar">
            <i class="fa-solid fa-circle-check"></i>
            Semua pesanan sudah dijemput.
          </div>
        @endforelse
      </div>
    </section>

    <section class="panel">
      <div class="item-kepala">
        <span class="bulat hijau"><i class="fa-solid fa-star"></i></span>
        <div>
          <strong>Kepuasan Pelanggan</strong>
          <div class="muted kecil">{{ $jumlahUlasan }} ulasan &middot; rata-rata {{ $rataRating }} dari 5</div>
        </div>
        <a href="{{ route('ulasan.index') }}" class="btn btn-garis btn-kecil" style="margin-left:auto">Lihat</a>
      </div>
    </section>
  </div>
</div>
@endsection

@push('script')
  <script src="{{ asset('js/dashboard-staff.js') }}"></script>
@endpush
