{{-- Pesan sukses / error dari session (flash data) --}}
@if (session('sukses'))
  <div class="alert alert-sukses">
    <i class="fa-solid fa-circle-check"></i>
    <span>{{ session('sukses') }}</span>
    <button type="button" class="tutup" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
  </div>
@endif

@if (session('error'))
  <div class="alert alert-error">
    <i class="fa-solid fa-circle-exclamation"></i>
    <span>{{ session('error') }}</span>
    <button type="button" class="tutup" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
  </div>
@endif

{{-- Pesan dari validasi ($request->validate) --}}
@if ($errors->any())
  <div class="alert alert-error">
    <i class="fa-solid fa-circle-exclamation"></i>
    <div>
      <strong>Data belum valid, cek lagi ya:</strong>
      <ul>
        @foreach ($errors->all() as $pesan)
          <li>{{ $pesan }}</li>
        @endforeach
      </ul>
    </div>
    <button type="button" class="tutup" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
  </div>
@endif
