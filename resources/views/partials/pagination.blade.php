@if ($paginator->hasPages())
  <nav class="paginasi">
    <span class="muted kecil">Menampilkan {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data</span>
    <div class="halaman">
      @if ($paginator->onFirstPage())
        <span class="no mati"><i class="fa-solid fa-chevron-left"></i></span>
      @else
        <a href="{{ $paginator->previousPageUrl() }}" aria-label="Sebelumnya"><i class="fa-solid fa-chevron-left"></i></a>
      @endif

      @for ($i = 1; $i <= $paginator->lastPage(); $i++)
        @if ($i == $paginator->currentPage())
          <span class="no aktif">{{ $i }}</span>
        @elseif ($i == 1 || $i == $paginator->lastPage() || abs($i - $paginator->currentPage()) <= 1)
          <a href="{{ $paginator->url($i) }}">{{ $i }}</a>
        @elseif (abs($i - $paginator->currentPage()) == 2)
          <span class="no mati">…</span>
        @endif
      @endfor

      @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" aria-label="Berikutnya"><i class="fa-solid fa-chevron-right"></i></a>
      @else
        <span class="no mati"><i class="fa-solid fa-chevron-right"></i></span>
      @endif
    </div>
  </nav>
@endif
