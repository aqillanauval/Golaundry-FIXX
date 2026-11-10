
document.addEventListener('DOMContentLoaded', function () {
  const pilihLayanan = document.getElementById('layanan');
  const inputQty = document.getElementById('qty');
  const pilihPromo = document.getElementById('promo');  
  const tempatHasil = document.getElementById('estimasi');

  if (!pilihLayanan || !inputQty || !tempatHasil) return;

  function hitung() {
    const opsi = pilihLayanan.options[pilihLayanan.selectedIndex];
    const harga = Number(opsi.dataset.harga || 0);
    const jumlah = parseFloat(inputQty.value) || 0;

    let persen = Number(tempatHasil.dataset.diskonTetap || 0);
    if (pilihPromo) {
      persen = Number(pilihPromo.options[pilihPromo.selectedIndex].dataset.diskon || 0);
    }

    const subtotal = Math.round(harga * jumlah);
    const total = subtotal - Math.round(subtotal * persen / 100);
    tempatHasil.textContent = 'Rp ' + total.toLocaleString('id-ID');
  }

  pilihLayanan.addEventListener('change', hitung);
  inputQty.addEventListener('input', hitung);
  if (pilihPromo) pilihPromo.addEventListener('change', hitung);
  hitung(); 
});
