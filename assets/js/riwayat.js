
document.addEventListener('DOMContentLoaded', function () {
  const formUlasan = document.getElementById('formUlasan');
  if (!formUlasan) return;

  formUlasan.scrollIntoView({ behavior: 'smooth', block: 'center' });
  const komentar = document.getElementById('komentar');
  if (komentar) komentar.focus();
});
