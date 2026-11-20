// =========================================================
// Hitung perkiraan harga di form pesanan / transaksi
// =========================================================

document.addEventListener('DOMContentLoaded', function () {
  const tempatHasil = document.querySelector('#estimasi');
  if (!tempatHasil) return;

  const inputBerat = document.querySelector('#berat');   // hanya ada di form transaksi
  const pilihPromo = document.querySelector('#promo_id');

  function ambilLayanan() {
    // bisa berupa radio (form pelanggan) atau select (form staff)
    const radio = document.querySelector('input[name="layanan_id"]:checked');
    if (radio) return radio;
    const select = document.querySelector('select[name="layanan_id"]');
    if (select && select.value !== '') return select.options[select.selectedIndex];
    return null;
  }

  function hitung() {
    const layanan = ambilLayanan();
    if (!layanan) {
      tempatHasil.textContent = 'Pilih layanan dulu';
      return;
    }

    const harga = Number(layanan.dataset.harga || 0);
    const satuan = layanan.dataset.satuan || 'kg';

    let diskon = Number(tempatHasil.dataset.diskonTetap || 0);
    if (pilihPromo && pilihPromo.value !== '') {
      diskon = Number(pilihPromo.options[pilihPromo.selectedIndex].dataset.diskon || 0);
    } else if (pilihPromo) {
      diskon = 0;
    }

    if (inputBerat) {
      // form staff: berat sudah diketahui, jadi hitung totalnya
      const berat = parseFloat(String(inputBerat.value).replace(',', '.')) || 0;
      const subtotal = Math.round(harga * berat);
      const total = subtotal - Math.round(subtotal * diskon / 100);
      tempatHasil.textContent = 'Rp ' + total.toLocaleString('id-ID');
    } else {
      // form pelanggan: berat belum ditimbang, tampilkan harga per satuan
      const hargaAkhir = Math.round(harga - harga * diskon / 100);
      tempatHasil.textContent = 'Rp ' + hargaAkhir.toLocaleString('id-ID') + ' / ' + satuan;
    }
  }

  document.querySelectorAll('input[name="layanan_id"], select[name="layanan_id"]').forEach(function (el) {
    el.addEventListener('change', hitung);
  });
  if (inputBerat) inputBerat.addEventListener('input', hitung);
  if (pilihPromo) pilihPromo.addEventListener('change', hitung);

  hitung();
});
