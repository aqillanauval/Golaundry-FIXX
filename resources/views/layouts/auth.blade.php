<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('judul') - GOLaundry</title>
  <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
  <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
  <div class="auth-shell">
    <div class="auth-gambar" style="background-image: url('{{ asset('img/login-banner.png') }}')">
      <div class="fitur">
        <span><i class="fa-solid fa-truck"></i> Antar-jemput</span>
        <span><i class="fa-solid fa-scale-balanced"></i> Ditimbang jujur</span>
        <span><i class="fa-solid fa-tag"></i> Promo mahasiswa</span>
      </div>
    </div>

    <div class="auth-form">
      <a href="{{ route('login') }}" class="brand">
        <img src="{{ asset('img/logo.png') }}" alt="Logo GOLaundry">
        <span>GO<b>Laundry</b></span>
      </a>

      @include('partials.flash')

      @yield('konten')
    </div>
  </div>
  <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
