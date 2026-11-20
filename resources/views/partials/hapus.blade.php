{{-- Tombol hapus dengan konfirmasi. Pakai: @include('partials.hapus', ['url' => ..., 'pesan' => ...]) --}}
<form method="POST" action="{{ $url }}" class="inline" data-konfirmasi="{{ $pesan }}">
  @csrf
  @method('DELETE')
  <button type="submit" class="btn btn-hapus btn-kecil {{ isset($teks) ? '' : 'btn-ikon' }}" title="Hapus">
    <i class="fa-solid fa-trash-can"></i>@isset($teks) {{ $teks }}@endisset
  </button>
</form>
