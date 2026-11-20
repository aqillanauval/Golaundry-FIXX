<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  {{-- token CSRF untuk AJAX --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('judul', 'Dashboard') - GOLaundry</title>
  <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
  <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
@php
  $user = auth()->user();
@endphp

<div class="shell">

  {{-- ================= SIDEBAR ================= --}}
  <aside class="sidebar" id="sidebar">
    <a href="{{ route('dashboard') }}" class="brand">
      <img src="{{ asset('img/logo.png') }}" alt="Logo GOLaundry">
      <span>GO<b>Laundry</b></span>
    </a>

    <nav class="menu">
      @foreach ($user->menu() as $judulGrup => $daftarMenu)
        <div class="menu-grup">
          <span class="menu-grup-judul">{{ $judulGrup }}</span>
          @foreach ($daftarMenu as $menu)
            {{-- $menu = [nama route, label, ikon, pola route aktif] --}}
            <a href="{{ route($menu[0]) }}" class="{{ request()->routeIs($menu[3]) ? 'aktif' : '' }}">
              <i class="fa-solid {{ $menu[2] }}"></i> {{ $menu[1] }}
            </a>
          @endforeach
        </div>
      @endforeach
    </nav>

    <div class="sidebar-bawah">
      @if (!$user->isAdmin())
        <div class="kartu-bantuan">
          <strong>Cucian numpuk?</strong>
          <small>Kurir kami siap jemput ke kos kamu.</small>
          <a href="{{ route('pesanan.index') }}"><i class="fa-solid fa-truck"></i> Pesan sekarang</a>
        </div>
      @endif

      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="tombol-logout">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </button>
      </form>
    </div>
  </aside>
  <div class="overlay" id="overlay"></div>

  {{-- ================= KONTEN ================= --}}
  <main class="main">
    <header class="topbar">
      <button type="button" class="menu-toggle" id="menuToggle" aria-label="Buka menu">
        <i class="fa-solid fa-bars"></i>
      </button>

      @if ($user->isAdmin())
        {{-- pencarian cepat: langsung mencari di halaman transaksi --}}
        <form action="{{ route('transaksi.index') }}" method="GET" class="cari-atas">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="search" name="cari" placeholder="Cari no. nota, pelanggan, layanan..." value="{{ request('cari') }}">
        </form>
      @else
        <span class="tanggal-atas"><i class="fa-regular fa-calendar"></i> {{ tanggalIndo() }}</span>
      @endif

      <div class="topbar-kanan">
        @if ($user->isAdmin())
          @php $jumlahJemput = \App\Models\Pesanan::where('status', 'Menunggu Jemput')->count(); @endphp
          <a href="{{ route('dashboard') }}#jemput" class="tombol-bulat" title="Pesanan menunggu dijemput">
            <i class="fa-regular fa-bell"></i>
            @if ($jumlahJemput > 0)<span class="titik">{{ $jumlahJemput }}</span>@endif
          </a>
        @endif
        <a href="{{ route('profil') }}" class="chip-user">
          <span class="avatar {{ $user->isAdmin() ? 'ungu' : '' }}">{{ inisial($user->nama) }}</span>
          <span class="teks-user">
            <strong>{{ $user->nama }}</strong>
            <small>{{ $user->namaRole() }}</small>
          </span>
        </a>
      </div>
    </header>

    @include('partials.flash')

    @yield('konten')
  </main>
</div>

{{-- Modal konfirmasi hapus (dipakai semua form data-konfirmasi) --}}
<div class="modal-latar" id="modalKonfirmasi">
  <div class="modal" role="dialog" aria-modal="true">
    <div class="ikon-modal"><i class="fa-solid fa-trash-can"></i></div>
    <h3>Yakin mau menghapus?</h3>
    <p id="teksKonfirmasi">Data yang dihapus tidak bisa dikembalikan.</p>
    <div class="aksi">
      <button type="button" class="btn btn-garis" id="batalHapus">Batal</button>
      <button type="button" class="btn btn-hapus" id="yakinHapus">Ya, hapus</button>
    </div>
  </div>
</div>

<script src="{{ asset('js/app.js') }}"></script>
@stack('script')
</body>
</html>
