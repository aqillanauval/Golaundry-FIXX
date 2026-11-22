<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nota {{ $pesanan->kode }} - GOLaundry</title>
  <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
  <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="nota-halaman">
  <div class="nota">
    <div class="nota-kepala">
      <img src="{{ asset('img/logo.png') }}" alt="Logo GOLaundry">
      <h2>GOLaundry</h2>
      <p class="muted kecil">Laundry antar-jemput mahasiswa &amp; anak kos</p>
    </div>

    <div style="padding:14px 0">
      <div class="nota-baris"><span>No. Nota</span><span class="kode">{{ $pesanan->kode }}</span></div>
      <div class="nota-baris"><span>Tanggal</span><span>{{ tglPendek($pesanan->tanggal, true) }}</span></div>
      <div class="nota-baris"><span>Pelanggan</span><span>{{ $pesanan->namaPelanggan() }}</span></div>
      <div class="nota-baris"><span>Asal pesanan</span><span>{{ $pesanan->asal() }}</span></div>
      <div class="nota-baris"><span>Status</span><span>@include('partials.status', ['status' => $pesanan->status])</span></div>
      @if ($pesanan->kurir)
        <div class="nota-baris"><span>Dijemput oleh</span><span>{{ $pesanan->kurir->nama }}</span></div>
      @endif
      @if ($pesanan->catatan != '')
        <div class="nota-baris"><span>{{ $pesanan->sumber == 'aplikasi' ? 'Alamat jemput' : 'Catatan' }}</span><span>{{ $pesanan->catatan }}</span></div>
      @endif
    </div>

    <div style="padding:14px 0;border-top:2px dashed var(--garis)">
      <div class="nota-baris"><span>Layanan</span><span>{{ $pesanan->nama_layanan }}</span></div>
      <div class="nota-baris"><span>Harga</span><span>{{ rupiah($pesanan->harga) }} / {{ $pesanan->satuan }}</span></div>
      <div class="nota-baris">
        <span>Berat / jumlah</span>
        <span>{{ $pesanan->sudahDitimbang() ? formatBerat($pesanan->berat) . ' ' . $pesanan->satuan : 'Belum ditimbang' }}</span>
      </div>
      <div class="nota-baris"><span>Subtotal</span><span>{{ rupiah($pesanan->subtotal()) }}</span></div>
      @if ($pesanan->punyaDiskon())
        <div class="nota-baris"><span>Promo {{ $pesanan->nama_promo }} ({{ $pesanan->diskon }}%)</span><span style="color:var(--hijau)">- {{ rupiah($pesanan->potongan()) }}</span></div>
      @endif
    </div>

    <div class="nota-total"><span>Total</span><span>{{ $pesanan->sudahDitimbang() ? rupiah($pesanan->total()) : '-' }}</span></div>
    <p class="muted kecil" style="text-align:center;margin-top:12px">Terima kasih sudah mempercayakan cucianmu ke GOLaundry 💜</p>
  </div>

  <div class="nota-aksi">
    <a href="{{ $linkKembali }}" class="btn btn-garis"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    <button type="button" class="btn btn-utama" onclick="window.print()"><i class="fa-solid fa-print"></i> Cetak Nota</button>
  </div>
</div>
</body>
</html>
