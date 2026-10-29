<?php
// ============================================================
// fungsi.php
// Kumpulan fungsi bantu yang dipakai bersama oleh semua halaman.
// Data disimpan di file .txt (satu baris = satu data, dipisah koma).
// ============================================================

// ---------- Fungsi umum ----------

// aman ditampilkan di HTML (mencegah XSS)
function e($teks)
{
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

function rupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// bersihkan teks sebelum disimpan ke .txt (koma & enter bisa merusak format file)
function bersihkan($teks)
{
    return trim(str_replace([',', "\r", "\n"], ' ', (string)$teks));
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function tanggalIndo()
{
    $hari  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
              'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return $hari[(int)date('w')] . ', ' . date('j') . ' ' . $bulan[(int)date('n')] . ' ' . date('Y');
}

// tanggal pendek berbahasa Indonesia, contoh: 13 Okt 2026 (jam: true -> tambah 09:30)
function tglPendek($waktu = null, $denganJam = false)
{
    $bln = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $waktu = $waktu ?? time();
    $hasil = date('d', $waktu) . ' ' . $bln[(int)date('n', $waktu)] . ' ' . date('Y', $waktu);
    return $denganJam ? $hasil . ' ' . date('H:i', $waktu) : $hasil;
}

function inisial($nama)
{
    $huruf = function_exists('mb_substr') ? mb_substr($nama, 0, 1) : substr($nama, 0, 1);
    return function_exists('mb_strtoupper') ? mb_strtoupper($huruf) : strtoupper($huruf);
}

// email dijadikan huruf kecil semua, nomor HP dibiarkan
function normalUser($username)
{
    $username = trim($username);
    return (strpos($username, '@') !== false) ? strtolower($username) : $username;
}

function usernameValid($username)
{
    $emailValid = filter_var($username, FILTER_VALIDATE_EMAIL) !== false;
    $hpValid = preg_match('/^\+?[0-9]{9,15}$/', $username) === 1;
    return $emailValid || $hpValid;
}

// ---------- State: SESSION (status login) ----------

function sudahLogin()
{
    return isset($_SESSION['user']);
}

function urlDashboard($role)
{
    return ($role === 'admin') ? 'dashboard-admin.php' : 'dashboard-customer.php';
}

// panggil di awal halaman yang butuh login.
// $role = 'admin' / 'customer' kalau halaman khusus role tertentu
function wajibLogin($role = null)
{
    if (!sudahLogin()) {
        setFlash('error', 'Silakan login dulu ya.');
        redirect('login.php');
    }
    if ($role !== null && $_SESSION['role'] !== $role) {
        redirect(urlDashboard($_SESSION['role']));
    }
}

// pesan singkat sekali tampil (disimpan di session, hilang setelah dibaca)
function setFlash($tipe, $pesan)
{
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function tampilFlash()
{
    if (!isset($_SESSION['flash'])) {
        return '';
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $kelas = ($f['tipe'] === 'error') ? 'alert-error' : 'alert-sukses';
    return '<div class="alert ' . $kelas . '" data-autohide>' . e($f['pesan']) . '</div>';
}

// ---------- File Handling ----------

// baca file .txt jadi array. Tiap baris dipecah dengan explode(',')
// kolom terakhir boleh berisi koma (karena explode dibatasi $jumlahKolom)
function bacaFile($namaFile, $jumlahKolom)
{
    return bacaFileFleksibel($namaFile, $jumlahKolom, $jumlahKolom);
}

// versi fleksibel: baris boleh punya kolom lebih sedikit ($minKolom sampai $maxKolom).
// Kolom yang kurang diisi teks kosong. Berguna kalau format file bertambah kolom baru,
// tapi data lama (kolomnya lebih sedikit) tetap bisa dibaca.
function bacaFileFleksibel($namaFile, $minKolom, $maxKolom)
{
    $hasil = [];
    $path = FOLDER_DATA . $namaFile;
    if (!file_exists($path)) {
        return $hasil;
    }
    $isi = file_get_contents($path);
    $baris = explode("\n", $isi);
    foreach ($baris as $b) {
        $b = trim($b);
        if ($b === '') {
            continue; // lewati baris kosong
        }
        $kolom = explode(',', $b, $maxKolom);
        if (count($kolom) >= $minKolom) {
            while (count($kolom) < $maxKolom) {
                $kolom[] = ''; // isi kolom yang kurang
            }
            $hasil[] = $kolom;
        }
    }
    return $hasil;
}

// tulis ulang seluruh isi file (dipakai setelah edit / hapus)
function tulisFile($namaFile, $baris)
{
    $teks = '';
    foreach ($baris as $kolom) {
        $teks .= implode(',', $kolom) . "\n";
    }
    file_put_contents(FOLDER_DATA . $namaFile, $teks, LOCK_EX);
}

// ---------- Users ----------

// Data akun disimpan di MySQL (tabel users). Bentuk array-nya sama seperti sebelumnya,
// jadi halaman lain tidak perlu diubah.
function loadUsers()
{
    global $pdo;
    $users = [];
    foreach ($pdo->query('SELECT username, password, nama, role FROM users ORDER BY id') as $k) {
        $users[$k['username']] = ['password' => $k['password'], 'nama' => $k['nama'], 'role' => $k['role']];
    }
    return $users;
}

// menyamakan isi tabel users dengan array $users: tambah/ubah yang ada, hapus yang sudah tidak ada
function saveUsers($users)
{
    global $pdo;
    $pdo->beginTransaction();
    try {
        $upsert = $pdo->prepare(
            'INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE password = VALUES(password), nama = VALUES(nama), role = VALUES(role)'
        );
        foreach ($users as $username => $u) {
            $upsert->execute([(string)$username, $u['password'], $u['nama'], $u['role']]);
        }
        if (count($users) > 0) {
            $marks = implode(',', array_fill(0, count($users), '?'));
            $hapus = $pdo->prepare("DELETE FROM users WHERE username NOT IN ($marks)");
            $hapus->execute(array_map('strval', array_keys($users)));
        } else {
            $pdo->exec('DELETE FROM users');
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// password baru disimpan dalam bentuk hash. Akun lama (belum di-hash) tetap bisa login.
function cekPassword($diketik, $tersimpan)
{
    if (substr($tersimpan, 0, 4) === '$2y$') {
        return password_verify($diketik, $tersimpan);
    }
    return hash_equals($tersimpan, $diketik);
}

function namaPengguna($username, $users)
{
    return isset($users[$username]) ? $users[$username]['nama'] : $username;
}

// ---------- Layanan ----------

function loadLayanan()
{
    $hasil = [];
    foreach (bacaFile('layanan.txt', 4) as $k) {
        $hasil[] = ['id' => $k[0], 'nama' => $k[1], 'harga' => (int)$k[2], 'satuan' => $k[3]];
    }
    return $hasil;
}

function saveLayanan($layanan)
{
    $baris = [];
    foreach ($layanan as $l) {
        $baris[] = [$l['id'], $l['nama'], $l['harga'], $l['satuan']];
    }
    tulisFile('layanan.txt', $baris);
}

function cariLayanan($id)
{
    foreach (loadLayanan() as $l) {
        if ($l['id'] === $id) {
            return $l;
        }
    }
    return null;
}

// ---------- Karyawan ----------

function loadKaryawan()
{
    $hasil = [];
    foreach (bacaFile('karyawan.txt', 4) as $k) {
        $hasil[] = ['id' => $k[0], 'nama' => $k[1], 'jabatan' => $k[2], 'no_hp' => $k[3]];
    }
    return $hasil;
}

function saveKaryawan($karyawan)
{
    $baris = [];
    foreach ($karyawan as $k) {
        $baris[] = [$k['id'], $k['nama'], $k['jabatan'], $k['no_hp']];
    }
    tulisFile('karyawan.txt', $baris);
}

// ---------- Pesanan / Transaksi ----------
// Format satu baris orders.txt:
// id,username,layanan,harga,satuan,qty,catatan,status,tanggal,diskon,promo
// (diskon = persen, promo = nama promo yang dipakai. Data lama tanpa 2 kolom ini tetap terbaca)

function loadOrders()
{
    $hasil = [];
    foreach (bacaFileFleksibel('orders.txt', 9, 11) as $k) {
        $hasil[] = [
            'id' => $k[0], 'username' => $k[1], 'layanan' => $k[2], 'harga' => (int)$k[3],
            'satuan' => $k[4], 'qty' => (float)$k[5], 'catatan' => $k[6],
            'status' => $k[7], 'tanggal' => $k[8],
            'diskon' => (int)$k[9], 'promo' => $k[10],
        ];
    }
    return $hasil;
}

function saveOrders($orders)
{
    $baris = [];
    foreach ($orders as $o) {
        $baris[] = [$o['id'], $o['username'], $o['layanan'], $o['harga'], $o['satuan'],
                    $o['qty'], $o['catatan'], $o['status'], $o['tanggal'],
                    (int)$o['diskon'], $o['promo']];
    }
    tulisFile('orders.txt', $baris);
}

// ----- Hitung uang pesanan (dipakai di semua halaman supaya angkanya selalu sama) -----

// harga x jumlah, sebelum diskon
function subtotalOrder($o)
{
    return (int)round($o['harga'] * $o['qty']);
}

// besar potongan dari promo
function potonganOrder($o)
{
    return (int)round(subtotalOrder($o) * $o['diskon'] / 100);
}

// yang harus dibayar pelanggan
function totalOrder($o)
{
    return subtotalOrder($o) - potonganOrder($o);
}

// cucian dianggap sudah ditimbang kalau beratnya sudah diisi (lebih dari 0)
function sudahDitimbang($o)
{
    return $o['qty'] > 0;
}

// teks berat untuk tabel: "3 kg", atau tulisan kecil kalau belum ditimbang
function beratLabel($o)
{
    if (!sudahDitimbang($o)) {
        return '<span class="tag-tunggu">Belum ditimbang</span>';
    }
    return e($o['qty']) . ' ' . e($o['satuan']);
}

// teks total untuk tabel: kalau pakai promo, ada tulisan kecil "Diskon ..."
function labelTotal($o)
{
    if (!sudahDitimbang($o)) {
        return '<span class="muted">Menunggu berat</span>';
    }
    $teks = '<strong>' . e(rupiah(totalOrder($o))) . '</strong>';
    if ($o['diskon'] > 0) {
        $teks .= '<span class="tag-promo">Diskon ' . (int)$o['diskon'] . '%</span>';
    }
    return $teks;
}

// "Menunggu Jemput" -> "menunggu-jemput" (dipakai untuk nama class CSS)
function statusKelas($status)
{
    return strtolower(str_replace(' ', '-', $status));
}

function badgeStatus($status)
{
    return '<span class="badge badge-' . e(statusKelas($status)) . '">' . e($status) . '</span>';
}

// pesanan ini dibuat pelanggan lewat aplikasi (username-nya akun terdaftar) atau dicatat admin di kios?
function sumberOrder($o, $users)
{
    return isset($users[$o['username']]) ? 'Aplikasi' : 'Kios';
}

// ---------- Ulasan ----------

function loadUlasan()
{
    $hasil = [];
    foreach (bacaFile('ulasan.txt', 3) as $k) {
        $hasil[$k[0]] = ['rating' => (int)$k[1], 'komentar' => $k[2]];
    }
    return $hasil;
}

function saveUlasan($ulasan)
{
    $baris = [];
    foreach ($ulasan as $idPesanan => $u) {
        $baris[] = [$idPesanan, $u['rating'], $u['komentar']];
    }
    tulisFile('ulasan.txt', $baris);
}

// ---------- Promo ----------

function loadPromo()
{
    $hasil = [];
    foreach (bacaFile('promo.txt', 6) as $k) {
        $hasil[] = [
            'id' => $k[0], 'judul' => $k[1], 'deskripsi' => $k[2], 'diskon' => (int)$k[3],
            'sampai' => $k[4], 'foto' => $k[5],
        ];
    }
    return $hasil;
}

function savePromo($promo)
{
    $baris = [];
    foreach ($promo as $p) {
        $baris[] = [$p['id'], $p['judul'], $p['deskripsi'], $p['diskon'], $p['sampai'], $p['foto']];
    }
    tulisFile('promo.txt', $baris);
}

function promoMasihBerlaku($promo)
{
    return $promo['sampai'] >= date('Y-m-d'); // format Y-m-d bisa dibandingkan langsung
}

// semua promo yang masih bisa dipakai hari ini
function promoAktifList()
{
    $hasil = [];
    foreach (loadPromo() as $p) {
        if (promoMasihBerlaku($p)) {
            $hasil[] = $p;
        }
    }
    return $hasil;
}

function cariPromo($id)
{
    foreach (loadPromo() as $p) {
        if ($p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

// ---------- Form hapus (dipakai di banyak tabel) ----------

function formHapus($nilai, $pesanKonfirmasi, $namaField = 'id', $teksTombol = 'Hapus')
{
    return '<form method="post" class="inline" data-konfirmasi="' . e($pesanKonfirmasi) . '">'
         . '<input type="hidden" name="aksi" value="hapus">'
         . '<input type="hidden" name="' . e($namaField) . '" value="' . e($nilai) . '">'
         . '<button type="submit" class="btn btn-sm btn-danger">' . e($teksTombol) . '</button>'
         . '</form>';
}

// ---------- Siapkan folder & file data ----------

function siapkanData()
{
    if (!is_dir(FOLDER_DATA)) {
        mkdir(FOLDER_DATA, 0777, true);
    }
    if (!is_dir(FOLDER_UPLOAD)) {
        mkdir(FOLDER_UPLOAD, 0777, true);
    }
    foreach (['layanan', 'karyawan', 'orders', 'ulasan', 'promo'] as $nama) {
        if (!file_exists(FOLDER_DATA . $nama . '.txt')) {
            file_put_contents(FOLDER_DATA . $nama . '.txt', '');
        }
    }
    // kalau tabel users masih kosong: impor akun lama dari users.txt (kalau ada),
    // kalau tidak ada buat 2 akun contoh (password di-hash)
    if (count(loadUsers()) === 0) {
        $lama = [];
        foreach (bacaFile('users.txt', 4) as $k) {
            $lama[$k[0]] = ['password' => $k[1], 'nama' => $k[2], 'role' => $k[3]];
        }
        if (count($lama) > 0) {
            saveUsers($lama);
            return;
        }
        saveUsers([
            'admin@golaundry.com' => [
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'nama' => 'Admin GoLaundry', 'role' => 'admin',
            ],
            'pelanggan@golaundry.com' => [
                'password' => password_hash('pelanggan123', PASSWORD_DEFAULT),
                'nama' => 'Pelanggan GoLaundry', 'role' => 'customer',
            ],
        ]);
    }
}

// ---------- Ikon (SVG kecil, tidak perlu internet) ----------

// ikon memakai Font Awesome (kit dipasang di <head> tiap halaman)
function ikon($nama, $ukuran = 20)
{
    $kelas = [
        'home' => 'fa-house',
        'tag' => 'fa-tag',
        'users' => 'fa-users',
        'user' => 'fa-user',
        'file' => 'fa-file-lines',
        'briefcase' => 'fa-briefcase',
        'gift' => 'fa-gift',
        'plus' => 'fa-circle-plus',
        'plusplain' => 'fa-plus',
        'minus' => 'fa-minus',
        'clock' => 'fa-clock',
        'logout' => 'fa-right-from-bracket',
        'menu' => 'fa-bars',
        'mail' => 'fa-envelope',
        'lock' => 'fa-lock',
        'eye' => 'fa-eye',
        'eyeoff' => 'fa-eye-slash',
        'search' => 'fa-magnifying-glass',
        'arrow' => 'fa-arrow-right',
        'printer' => 'fa-print',
        'cart' => 'fa-cart-shopping',
        'wallet' => 'fa-wallet',
        'check' => 'fa-check',
        'image' => 'fa-image',
        'percent' => 'fa-percent',
        'shirt' => 'fa-shirt',
        'droplet' => 'fa-droplet',
        'truck' => 'fa-truck',
        'scale' => 'fa-scale-balanced',
        'store' => 'fa-store',
        'pin' => 'fa-location-dot',
        'pen' => 'fa-pen',
    ];
    $fa = isset($kelas[$nama]) ? $kelas[$nama] : 'fa-circle';
    $px = round($ukuran * 0.85);
    return '<i class="fa-solid ' . $fa . ' fa-fw ikon-fa" style="font-size:' . $px . 'px" aria-hidden="true"></i>';
}

// logo mesin cuci kecil
function logoSvg($ukuran = 44)
{
    return '<img src="assets/img/logo.png" width="' . $ukuran . '" height="' . $ukuran . '" alt="Logo GOLaundry" class="logo-img">';
}
