// ============================================================
// dashboard-admin.js  -  AJAX: ubah status pesanan tanpa reload halaman
// Alurnya: dropdown berubah -> fetch (POST) ke dashboard-admin.php
//          -> PHP membalas JSON -> tampilkan toast.
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
  const semuaDropdown = document.querySelectorAll('.select-status');

  semuaDropdown.forEach(function (dropdown) {
    let statusSebelumnya = dropdown.value;

    dropdown.addEventListener('change', function () {
      const data = new URLSearchParams();
      data.append('aksi', 'ubah_status');
      data.append('id', dropdown.dataset.id);
      data.append('status', dropdown.value);

      fetch('dashboard-admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: data.toString()
      })
        .then(function (res) { return res.json(); })
        .then(function (hasil) {
          if (hasil.sukses) {
            // ganti warna dropdown sesuai status baru
            dropdown.className = 'select-status status-' + dropdown.value.toLowerCase().replace(/ /g, '-');
            statusSebelumnya = dropdown.value;
            tampilToast(hasil.pesan, 'sukses');
          } else {
            dropdown.value = statusSebelumnya; // kembalikan pilihan lama
            tampilToast(hasil.pesan, 'error');
          }
        })
        .catch(function () {
          dropdown.value = statusSebelumnya;
          tampilToast('Terjadi kesalahan, silakan coba lagi.', 'error');
        });
    });
  });
});
