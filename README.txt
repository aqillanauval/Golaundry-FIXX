GOLaundry - Laundry Management (revisi 3)
=========================================

CARA MENJALANKAN
1. Taruh folder PemrogramanWeb di htdocs (XAMPP), nyalakan Apache.
2. Buka di browser:  http://localhost/PemrogramanWeb/
   Selalu masuk ke halaman LOGIN lebih dulu (sesi lama otomatis dihapus).

AKUN CONTOH
- Admin     : admin@golaundry.com     / admin123
- Pelanggan : pelanggan@golaundry.com / pelanggan123
Akun baru yang mendaftar otomatis menjadi pelanggan. Role dibaca otomatis saat login.

GAMBAR LOGIN / REGISTER
Poster login/register : assets/img/login-banner.png (sudah terpasang)
Logo                  : assets/img/logo.png (dipakai di sidebar, login, nota, dan favicon)
Ikon                  : Font Awesome lewat kit di <head> tiap halaman (butuh internet).
                        Ikon dipanggil lewat fungsi ikon('nama') di includes/fungsi.php.

ALUR PESANAN (BARU)
1. Pelanggan buka Buat Pesanan: pilih JENIS LAYANAN saja + isi alamat jemput (tidak perlu isi berat,
   karena pelanggan belum tahu beratnya). Promo boleh dipilih. Status awal: "Menunggu Jemput".
   Alamat jemput disimpan di kolom "catatan" pada orders.txt.
2. Staff/kurir menjemput cucian, menimbang, lalu isi berat di Dashboard Admin (bagian "Perlu dijemput").
   Status otomatis jadi "Diterima".
3. Dashboard pelanggan langsung menampilkan berat, harga per kg, subtotal, dan total bayar
   (sudah dipotong promo kalau memakai promo). Sebelum ditimbang tampil "Menunggu berat".
4. Dua jalur di Dashboard Admin:
   - Pesanan dari aplikasi : pesanan pelanggan, lalu dijemput dan ditimbang staff/kurir.
   - Pelanggan datang ke kios : staff mengisi sendiri lewat menu Transaksi (berat wajib diisi).
   Kolom "Asal" membedakan Aplikasi dan Kios.
5. Status: Menunggu Jemput > Diterima > Dicuci > Selesai > Diambil.
   Pelanggan hanya boleh edit/batalkan pesanan saat masih "Menunggu Jemput".
   Nota baru bisa dicetak setelah cucian ditimbang.
- qty = 0 di orders.txt artinya belum ditimbang.

TAMPILAN BARU
Konsep "tiket cucian": biru laut + kuning label untuk promo, font Bricolage Grotesque + Figtree
(dimuat dari Google Fonts, jadi butuh internet; kalau offline otomatis pakai font bawaan).

FITUR PROMO / DISKON
- Admin mengatur promo di menu Promo (tambah, edit, hapus, upload foto).
- Pelanggan memilih promo di halaman Buat Pesanan (langkah 3) atau lewat tombol
  "Pakai promo ini" di dashboard. Diskon dihitung otomatis dan tampil di nota.
- Admin juga bisa memilih promo saat mencatat transaksi manual.
- Format orders.txt sekarang 11 kolom (ditambah: diskon, promo).
  Data lama yang masih 9 kolom tetap terbaca (dianggap tanpa diskon).

STRUKTUR
index.php, login.php, register.php, logout.php ... -> halaman (HTML + PHP jadi satu)
includes/   -> config, fungsi bantu, header, footer, kartu promo
assets/css  -> style.css (aplikasi), auth.css (login & register)
assets/js   -> app.js (umum), auth.js, order.js, estimasi.js, dashboard-admin.js (AJAX), promo.js, riwayat.js
data/       -> file .txt sebagai penyimpanan data (dilindungi .htaccess)
uploads/promo/ -> foto promo hasil upload
