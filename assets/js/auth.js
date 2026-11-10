// ============================================================
// auth.js  -  JavaScript khusus halaman login & register
// Validasi di sini hanya untuk kenyamanan pengguna (cepat, tanpa reload).
// Validasi yang sebenarnya tetap dilakukan di server (PHP).
// ============================================================
document.addEventListener('DOMContentLoaded', function () {

  const form = document.getElementById('formLogin') || document.getElementById('formRegister');
  const teksError = document.getElementById('errorForm');

  // 1. Kalau gambar banner belum ada, sembunyikan supaya tampilan cadangan muncul
  const gambar = document.querySelector('[data-gambar-banner]');
  if (gambar) {
    const sembunyikan = function () { gambar.style.display = 'none'; };
    gambar.addEventListener('error', sembunyikan);
    if (gambar.complete && gambar.naturalWidth === 0) {
      sembunyikan(); // error sudah terjadi sebelum script ini jalan
    }
  }

  // 2. Tombol / link yang fiturnya belum dibuat (Google & Lupa password)
  const belumTersedia = document.querySelectorAll('[data-belum-tersedia]');
  belumTersedia.forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      tampilToast('Fitur ini belum tersedia.', 'error');
    });
  });

  if (!form) return;

  // 3. Validasi sebelum form dikirim
  form.addEventListener('submit', function (e) {
    teksError.textContent = '';
    const pesan = cekForm();

    if (pesan !== '') {
      e.preventDefault(); // jangan kirim ke server
      teksError.textContent = pesan;
    }
  });

  function cekForm() {
    const username = form.querySelector('#username').value.trim();
    const password = form.querySelector('#password').value;

    // halaman login: cukup cek tidak kosong
    if (form.id === 'formLogin') {
      if (username === '' || password === '') return 'Email/No. HP dan password wajib diisi ya.';
      return '';
    }

    // halaman register: cek lebih lengkap
    const nama = form.querySelector('#nama').value.trim();
    const ulangi = form.querySelector('#ulangi').value;

    if (nama === '' || username === '' || password === '' || ulangi === '') {
      return 'Semua kolom wajib diisi ya.';
    }

    const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(username);
    const hpValid = /^\+?[0-9]{9,15}$/.test(username);
    if (!emailValid && !hpValid) return 'Isi dengan email yang valid atau nomor HP (9-15 angka).';

    if (password.length < 6) return 'Password minimal 6 karakter.';
    if (password !== ulangi) return 'Ulangi password belum sama.';

    return '';
  }

  // 4. Halaman register: cek "ulangi password" langsung saat mengetik
  const ulangi = form.querySelector('#ulangi');
  if (ulangi) {
    ulangi.addEventListener('input', function () {
      const sama = ulangi.value === form.querySelector('#password').value;
      teksError.textContent = (ulangi.value !== '' && !sama) ? 'Ulangi password belum sama.' : '';
    });
  }
});
