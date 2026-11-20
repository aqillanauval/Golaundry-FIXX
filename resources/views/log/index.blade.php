@extends('layouts.app')

@section('judul', 'Log Aktivitas')

@section('konten')
<div class="judul-halaman">
  <h1>Log Aktivitas</h1>
  <p>Catatan siapa melakukan apa dan kapan. Disimpan di file <span class="kode">storage/logs/aktivitas.log</span>.</p>
</div>

<section class="panel">
  <div class="panel-kepala">
    <div><h3>300 catatan terakhir</h3><p>Ukuran file: {{ number_format($ukuran / 1024, 1, ',', '.') }} KB</p></div>
    @if (count($catatan) > 0)
      <form method="POST" action="{{ route('log.destroy') }}" data-konfirmasi="Semua isi file log akan dikosongkan.">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-hapus btn-kecil"><i class="fa-solid fa-broom"></i> Kosongkan Log</button>
      </form>
    @endif
  </div>

  <div class="tabel-wrap">
    <table>
      <thead><tr><th>Waktu</th><th>Pelaku</th><th>Aksi</th><th>Detail</th></tr></thead>
      <tbody>
        @forelse ($catatan as $c)
          <tr>
            <td class="muted kecil nowrap">{{ tglPendek($c['waktu'], true) }}</td>
            <td>{{ $c['pelaku'] }}</td>
            <td><span class="tag {{ str_contains($c['aksi'], 'gagal') ? 'habis' : '' }}">{{ $c['aksi'] }}</span></td>
            <td class="muted">{{ $c['detail'] }}</td>
          </tr>
        @empty
          <tr><td colspan="4" class="kosong">Log masih kosong.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</section>
@endsection
