// ============================================================
// order.js  -  Halaman Buat Pesanan (pelanggan)
// Berat cucian belum diketahui saat memesan (ditimbang kurir),
// jadi yang dihitung di sini adalah HARGA PER SATUAN setelah promo.
// Angka ini hanya tampilan. Diskon yang disimpan tetap dihitung server.
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('formPesan');
  if (!form) return;

  const tombolPesan = document.getElementById('tombolPesan');
  const rLayanan = document.getElementById('rLayanan');
  const rHarga = document.getElementById('rHarga');
  const rSatuan = document.getElementById('rSatuan');
  const rBarisDiskon = document.getElementById('rBarisDiskon');
  const rNamaPromo = document.getElementById('rNamaPromo');
  const rDiskon = document.getElementById('rDiskon');
  const rTotal = document.getElementById('rTotal');

  function rupiah(angka) {
    return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
  }

  function hitung() {
    const layanan = form.querySelector('input[name="layanan"]:checked');
    const promo = form.querySelector('input[name="promo"]:checked');

    if (!layanan) {
      rLayanan.textContent = 'Belum dipilih';
      rHarga.textContent = '-';
      rTotal.textContent = '-';
      rBarisDiskon.hidden = true;
      tombolPesan.disabled = true;
      return;
    }

    const harga = Number(layanan.dataset.harga);
    const persen = promo ? Number(promo.dataset.diskon) : 0;
    const potongan = Math.round(harga * persen / 100);

    rLayanan.textContent = layanan.dataset.nama;
    rSatuan.textContent = layanan.dataset.satuan;
    rHarga.textContent = rupiah(harga) + ' / ' + layanan.dataset.satuan;

    if (persen > 0) {
      rBarisDiskon.hidden = false;
      rNamaPromo.textContent = 'Promo ' + promo.dataset.nama;
      rDiskon.textContent = '-' + persen + '%';
    } else {
      rBarisDiskon.hidden = true;
    }
    rTotal.textContent = rupiah(harga - potongan);
    tombolPesan.disabled = false;
  }

  form.addEventListener('change', hitung);
  hitung(); // hitung sekali saat halaman dibuka (berguna saat mode edit / promo dari dashboard)
});
