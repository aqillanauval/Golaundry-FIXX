// =========================================================
// AJAX: ubah status pesanan tanpa reload halaman
// Dipakai di dashboard staff dan halaman transaksi.
// =========================================================

document.addEventListener('DOMContentLoaded', function () {
  const tokenCsrf = document.querySelector('meta[name="csrf-token"]').content;

  document.querySelectorAll('.select-status').forEach(function (dropdown) {
    let statusSebelumnya = dropdown.value;

    dropdown.addEventListener('change', function () {
      const data = new URLSearchParams();
      data.append('status', dropdown.value);

      // Alamat tujuan diambil dari atribut data-url (dibuat dengan route() di Blade)
      fetch(dropdown.dataset.url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-CSRF-TOKEN': tokenCsrf, // token CSRF Laravel
          'Accept': 'application/json'
        },
        body: data.toString()
      })
        .then(function (respon) { return respon.json(); })
        .then(function (hasil) {
          if (hasil.sukses) {
            statusSebelumnya = dropdown.value;
            // ganti juga label status di baris yang sama (kalau ada)
            const label = dropdown.closest('tr') ? dropdown.closest('tr').querySelector('.status') : null;
            if (label) {
              label.className = 'status status-' + dropdown.value.toLowerCase().replace(/ /g, '-');
              label.textContent = dropdown.value;
            }
            tampilToast(hasil.pesan, 'sukses');
          } else {
            dropdown.value = statusSebelumnya;
            tampilToast(hasil.pesan, 'error');
          }
        })
        .catch(function () {
          dropdown.value = statusSebelumnya;
          tampilToast('Gagal menyimpan. Coba refresh halaman lalu ulangi.', 'error');
        });
    });
  });
});
