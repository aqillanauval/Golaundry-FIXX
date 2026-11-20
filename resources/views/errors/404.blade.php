<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Halaman tidak ditemukan - GOLaundry</title>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
  <div class="nota-halaman" style="text-align:center;margin-top:12vh">
    <div class="nota">
      <img src="{{ asset('img/logo.png') }}" alt="Logo GOLaundry" style="width:64px">
      <h1 style="font-size:54px;margin-top:6px">404</h1>
      <h2 style="font-size:20px">Halaman tidak ditemukan</h2>
      <p class="muted" style="margin:8px 0 20px">Halaman atau data yang kamu cari tidak ada, atau sudah dihapus.</p>
      <a href="{{ url('/dashboard') }}" class="btn btn-utama">Kembali ke Dashboard</a>
    </div>
  </div>
</body>
</html>
