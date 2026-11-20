document.addEventListener('DOMContentLoaded', function () {

  const sidebar = document.querySelector('#sidebar');
  const overlay = document.querySelector('#overlay');
  const tombolMenu = document.querySelector('#menuToggle');

  if (tombolMenu && sidebar && overlay) {
    tombolMenu.addEventListener('click', function () {
      sidebar.classList.add('terbuka');
      overlay.classList.add('tampil');
    });
    overlay.addEventListener('click', function () {
      sidebar.classList.remove('terbuka');
      overlay.classList.remove('tampil');
    });
  }

  document.querySelectorAll('.alert .tutup').forEach(function (tombol) {
    tombol.addEventListener('click', function () {
      tombol.closest('.alert').remove();
    });
  });

  document.querySelectorAll('.alert-sukses').forEach(function (alert) {
    setTimeout(function () { alert.remove(); }, 5000);
  });

  document.querySelectorAll('.lihat-password').forEach(function (tombol) {
    tombol.addEventListener('click', function () {
      const input = tombol.parentElement.querySelector('input');
      const ikon = tombol.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        ikon.className = 'fa-solid fa-eye-slash';
      } else {
        input.type = 'password';
        ikon.className = 'fa-solid fa-eye';
      }
    });
  });
  
  const modal = document.querySelector('#modalKonfirmasi');
  let formMenunggu = null;

  document.querySelectorAll('form[data-konfirmasi]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (form.dataset.sudahYakin === 'ya') {
        return; 
      }
      e.preventDefault();

      if (!modal) {
        if (confirm(form.dataset.konfirmasi)) {
          form.dataset.sudahYakin = 'ya';
          form.submit();
        }
        return;
      }

      formMenunggu = form;
      modal.querySelector('#teksKonfirmasi').textContent = form.dataset.konfirmasi;
      modal.classList.add('tampil');
    });
  });

  if (modal) {
    modal.querySelector('#batalHapus').addEventListener('click', function () {
      modal.classList.remove('tampil');
      formMenunggu = null;
    });
    modal.querySelector('#yakinHapus').addEventListener('click', function () {
      if (formMenunggu) {
        formMenunggu.dataset.sudahYakin = 'ya';
        formMenunggu.submit();
      }
    });
  }
});

function tampilToast(pesan, tipe) {
  const toast = document.createElement('div');
  toast.className = 'toast' + (tipe === 'error' ? ' error' : '');
  toast.textContent = pesan; 
  document.body.appendChild(toast);

  setTimeout(function () { toast.classList.add('tampil'); }, 10);
  setTimeout(function () {
    toast.classList.remove('tampil');
    setTimeout(function () { toast.remove(); }, 300);
  }, 3000);
}
