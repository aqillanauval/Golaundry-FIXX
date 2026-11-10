// ============================================================
// promo.js  -  Pratinjau foto sebelum diunggah + cek ukuran file
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
  const inputFoto = document.getElementById('foto');
  const kotakPreview = document.getElementById('previewFoto');
  const gambarPreview = document.getElementById('previewImg');

  if (!inputFoto) return;

  inputFoto.addEventListener('change', function () {
    const file = inputFoto.files[0];
    if (!file) return;

    // cek cepat di browser (server tetap akan mengecek ulang)
    if (file.size > 2 * 1024 * 1024) {
      tampilToast('Ukuran foto melebihi 2 MB.', 'error');
      inputFoto.value = '';
      return;
    }

    gambarPreview.src = URL.createObjectURL(file);
    kotakPreview.hidden = false;
  });
});
