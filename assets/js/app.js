// ============================================================
// app.js  -  JavaScript umum, dipakai di semua halaman
// ============================================================

// Menampilkan pesan kecil (toast) di pojok layar
function tampilToast(pesan, tipe) {
  let wadah = document.getElementById('wadahToast');
  if (!wadah) {
    wadah = document.createElement('div');
    wadah.id = 'wadahToast';
    wadah.className = 'wadah-toast';
    document.body.appendChild(wadah);
  }

  const toast = document.createElement('div');
  toast.className = 'toast ' + (tipe === 'error' ? 'toast-error' : 'toast-sukses');
  toast.textContent = pesan;
  wadah.appendChild(toast);

  // munculkan, lalu hilangkan setelah 3 detik
  setTimeout(function () { toast.classList.add('tampil'); }, 10);
  setTimeout(function () {
    toast.classList.remove('tampil');
    setTimeout(function () { toast.remove(); }, 300);
  }, 3000);
}

document.addEventListener('DOMContentLoaded', function () {

  // 1. Buka / tutup sidebar di layar kecil (HP)
  const tombolMenu = document.getElementById('menuToggle');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');

  if (tombolMenu && sidebar) {
    tombolMenu.addEventListener('click', function () {
      sidebar.classList.toggle('buka');
      overlay.classList.toggle('tampil');
    });
    overlay.addEventListener('click', function () {
      sidebar.classList.remove('buka');
      overlay.classList.remove('tampil');
    });
  }

  // 2. Minta konfirmasi sebelum form dikirim (dipakai tombol Hapus / Batalkan)
  const formKonfirmasi = document.querySelectorAll('form[data-konfirmasi]');
  formKonfirmasi.forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!confirm(form.dataset.konfirmasi)) {
        e.preventDefault(); // batal kirim
      }
    });
  });

  // 3. Pesan sukses / error hilang sendiri setelah beberapa detik
  const pesanOtomatis = document.querySelectorAll('[data-autohide]');
  pesanOtomatis.forEach(function (pesan) {
    setTimeout(function () {
      pesan.classList.add('hilang');
    }, 4500);
  });

  // 4. Kolom pencarian untuk tabel: <input data-cari="idTabel">
  const kolomCari = document.querySelectorAll('[data-cari]');
  kolomCari.forEach(function (input) {
    input.addEventListener('input', function () {
      const kata = input.value.toLowerCase();
      const baris = document.querySelectorAll('#' + input.dataset.cari + ' tbody tr');

      baris.forEach(function (tr) {
        if (tr.hasAttribute('data-kosong')) return; // baris "belum ada data" dilewati
        const cocok = tr.textContent.toLowerCase().indexOf(kata) !== -1;
        tr.style.display = cocok ? '' : 'none';
      });
    });
  });

  // 5. Tombol lihat / sembunyikan password
  const tombolMata = document.querySelectorAll('[data-toggle-password]');
  tombolMata.forEach(function (tombol) {
    tombol.addEventListener('click', function () {
      const input = document.getElementById(tombol.dataset.togglePassword);
      const iconTutup = tombol.querySelector('.ikon-mata');
      const iconBuka = tombol.querySelector('.ikon-mata-buka');

      if (input.type === 'password') {
        input.type = 'text';
        iconTutup.hidden = true;
        iconBuka.hidden = false;
      } else {
        input.type = 'password';
        iconTutup.hidden = false;
        iconBuka.hidden = true;
      }
    });
  });

  // 6. Tombol cetak nota
  const tombolCetak = document.querySelector('[data-cetak]');
  if (tombolCetak) {
    tombolCetak.addEventListener('click', function () {
      window.print();
    });
  }
});
