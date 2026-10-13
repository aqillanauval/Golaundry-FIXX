// ============================================================
// transaksi.js  -  Filter cepat di tabel riwayat transaksi
// (Semua / Dari aplikasi / Di kios / Perlu dijemput).
// Baris tabel punya atribut data-asal dan data-status dari PHP.
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
  const wadah = document.getElementById('filterTransaksi');
  if (!wadah) return;

  const tombol = wadah.querySelectorAll('button');
  const baris = document.querySelectorAll('#tabelTransaksi tbody tr');

  tombol.forEach(function (btn) {
    btn.addEventListener('click', function () {
      const pilihan = btn.dataset.filter;

      tombol.forEach(function (b) { b.classList.remove('aktif'); });
      btn.classList.add('aktif');

      baris.forEach(function (tr) {
        if (tr.hasAttribute('data-kosong')) return;
        let cocok = true;
        if (pilihan === 'aplikasi' || pilihan === 'kios') cocok = tr.dataset.asal === pilihan;
        if (pilihan === 'menunggu') cocok = tr.dataset.status === 'menunggu';
        tr.style.display = cocok ? '' : 'none';
      });
    });
  });
});
