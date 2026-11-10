<?php
// ============================================================
// ImporModel.php  -  Memindahkan data lama dari file .txt (folder data/)
//                    ke database. Dipakai sekali saat migrasi.
//
// Modul 9 (Operasi File): file_exists(), filesize(), fopen('r'), fgets(), explode()
// Materi Database        : SELURUH impor dibungkus SATU TRANSAKSI.
//   Kalau ada satu baris saja yang gagal, rollBack() membatalkan semuanya,
//   jadi database tidak pernah berisi data setengah jadi.
// Data yang sudah ada (username / kode nota / nama sama) dilewati,
// jadi impor aman dijalankan lebih dari sekali.
// ============================================================

class ImporModel extends Model
{
    // file lama => jumlah kolom per baris (kolom terakhir boleh berisi koma)
    public const FILE = [
        'users.txt'    => 4,  // username,password,nama,role
        'layanan.txt'  => 4,  // id,nama,harga,satuan
        'karyawan.txt' => 4,  // id,nama,jabatan,no_hp
        'promo.txt'    => 6,  // id,judul,deskripsi,diskon,sampai,foto
        'orders.txt'   => 11, // id,username,layanan,harga,satuan,qty,catatan,status,tanggal,diskon,promo
        'ulasan.txt'   => 3,  // id_pesanan,rating,komentar
    ];

    // baca file .txt jadi array baris (tiap baris dipecah dengan explode)
    public function bacaTxt(string $namaFile, int $maxKolom, int $minKolom = 0): array
    {
        $path = FOLDER_DATA_LAMA . $namaFile;
        $hasil = [];
        if (!file_exists($path)) {
            return $hasil;
        }
        $fh = fopen($path, 'r');
        if ($fh === false) {
            return $hasil;
        }
        while (($baris = fgets($fh)) !== false) {
            $baris = trim($baris);
            if ($baris === '') {
                continue;
            }
            $kolom = explode(',', $baris, $maxKolom);
            if (count($kolom) >= ($minKolom ?: $maxKolom)) {
                $hasil[] = array_pad($kolom, $maxKolom, '');
            }
        }
        fclose($fh);
        return $hasil;
    }

    // ringkasan isi folder data/ untuk ditampilkan sebelum impor
    public function ringkasan(): array
    {
        $hasil = [];
        foreach (self::FILE as $nama => $kolom) {
            $path = FOLDER_DATA_LAMA . $nama;
            $ada = file_exists($path);
            $min = ($nama === 'orders.txt') ? 9 : 0; // orders versi lama hanya 9 kolom
            $hasil[] = [
                'file'   => $nama,
                'ada'    => $ada,
                'ukuran' => $ada ? filesize($path) : 0,
                'baris'  => $ada ? count($this->bacaTxt($nama, $kolom, $min)) : 0,
            ];
        }
        return $hasil;
    }

    // "26 Sep 2026 16:43" -> "2026-09-26 16:43:00"
    private function tanggalLama(string $teks): string
    {
        $bulan = ['Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'Mei' => 5, 'Jun' => 6, 'Jul' => 7,
                  'Agu' => 8, 'Sep' => 9, 'Okt' => 10, 'Nov' => 11, 'Des' => 12];
        if (preg_match('/^(\d{1,2}) (\w{3}) (\d{4})(?: (\d{1,2}):(\d{2}))?/', trim($teks), $m) && isset($bulan[$m[2]])) {
            return sprintf('%04d-%02d-%02d %02d:%02d:00', $m[3], $bulan[$m[2]], $m[1], $m[4] ?? 0, $m[5] ?? 0);
        }
        return date('Y-m-d H:i:s');
    }

    // ------------------------------------------------------------
    // Jalankan impor. Mengembalikan jumlah baris baru per tabel.
    // ------------------------------------------------------------
    public function jalankanImpor(): array
    {
        $jumlah = ['users' => 0, 'layanan' => 0, 'karyawan' => 0, 'promo' => 0, 'pesanan' => 0, 'ulasan' => 0];

        try {
            $this->db->beginTransaction();

            // ----- users -----
            $idUser = []; // username -> id
            foreach ($this->bacaTxt('users.txt', 4) as [$username, $password, $nama, $role]) {
                $username = normalUser($username);
                $ada = $this->satuBaris('SELECT id FROM users WHERE username = :u', ['u' => $username]);
                if ($ada) {
                    $idUser[$username] = (int)$ada['id'];
                    continue;
                }
                // akun lama yang password-nya masih teks biasa langsung di-hash
                $hash = (substr($password, 0, 4) === '$2y$') ? $password : password_hash($password, PASSWORD_DEFAULT);
                $this->jalankan(
                    'INSERT INTO users (username, password, nama, role) VALUES (:u, :p, :n, :r)',
                    ['u' => $username, 'p' => $hash, 'n' => $nama, 'r' => $role === 'admin' ? 'admin' : 'customer']
                );
                $idUser[$username] = (int)$this->db->lastInsertId();
                $jumlah['users']++;
            }

            // ----- layanan (dicocokkan lewat nama) -----
            $layananByNama = [];
            foreach ($this->bacaTxt('layanan.txt', 4) as [, $nama, $harga, $satuan]) {
                $ada = $this->satuBaris('SELECT * FROM layanan WHERE nama = :n', ['n' => $nama]);
                if (!$ada) {
                    $this->jalankan(
                        'INSERT INTO layanan (nama, harga, satuan) VALUES (:n, :h, :s)',
                        ['n' => $nama, 'h' => max(1, (int)$harga), 's' => $satuan]
                    );
                    $jumlah['layanan']++;
                }
            }
            foreach ($this->semuaBaris('SELECT * FROM layanan') as $l) {
                $layananByNama[$l['nama']] = $l;
            }

            // ----- karyawan -----
            foreach ($this->bacaTxt('karyawan.txt', 4) as [, $nama, $jabatan, $noHp]) {
                $ada = $this->satuBaris(
                    'SELECT id FROM karyawan WHERE nama = :n AND no_hp = :h',
                    ['n' => $nama, 'h' => $noHp]
                );
                if (!$ada) {
                    $this->jalankan(
                        'INSERT INTO karyawan (nama, jabatan, no_hp) VALUES (:n, :j, :h)',
                        ['n' => $nama, 'j' => $jabatan, 'h' => $noHp]
                    );
                    $jumlah['karyawan']++;
                }
            }

            // ----- promo (dicocokkan lewat judul) -----
            foreach ($this->bacaTxt('promo.txt', 6) as [, $judul, $deskripsi, $diskon, $sampai, $foto]) {
                if ($this->satuBaris('SELECT id FROM promo WHERE judul = :j', ['j' => $judul])) {
                    continue;
                }
                $this->jalankan(
                    'INSERT INTO promo (judul, deskripsi, diskon, berlaku_sampai, foto) VALUES (:j, :d, :k, :s, :f)',
                    ['j' => $judul, 'd' => $deskripsi, 'k' => min(100, max(1, (int)$diskon)),
                     's' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai) ? $sampai : date('Y-m-d'), 'f' => basename($foto)]
                );
                $jumlah['promo']++;
            }
            $promoByJudul = [];
            foreach ($this->semuaBaris('SELECT id, judul FROM promo') as $p) {
                $promoByJudul[$p['judul']] = (int)$p['id'];
            }

            // ----- pesanan (orders.txt, 9 atau 11 kolom) -----
            $idPesanan = []; // kode lama -> id baru
            $statusBoleh = Pesanan::DAFTAR_STATUS;
            foreach ($this->bacaTxt('orders.txt', 11, 9) as $k) {
                [$kode, $username, $layanan, $harga, $satuan, $qty, $catatan, $status, $tanggal, $diskon, $promo] = $k;
                $ada = $this->satuBaris('SELECT id FROM pesanan WHERE kode = :k', ['k' => $kode]);
                if ($ada) {
                    $idPesanan[$kode] = (int)$ada['id'];
                    continue;
                }
                $userId = $idUser[normalUser($username)] ?? null;
                $this->jalankan(
                    'INSERT INTO pesanan
                       (kode, user_id, nama_pelanggan, sumber, layanan_id, nama_layanan, harga, satuan,
                        berat, catatan, status, promo_id, nama_promo, diskon, tanggal)
                     VALUES (:kode, :uid, :np, :sumber, :lid, :nl, :harga, :satuan,
                             :berat, :catatan, :status, :pid, :npromo, :diskon, :tanggal)',
                    [
                        'kode'    => substr($kode, 0, 20),
                        'uid'     => $userId,
                        'np'      => $userId ? $this->namaUser($userId) : $username,
                        'sumber'  => $userId ? 'aplikasi' : 'kios',
                        'lid'     => isset($layananByNama[$layanan]) ? (int)$layananByNama[$layanan]['id'] : null,
                        'nl'      => $layanan,
                        'harga'   => max(0, (int)$harga),
                        'satuan'  => $satuan,
                        'berat'   => max(0, round((float)$qty, 1)),
                        'catatan' => $catatan,
                        'status'  => in_array($status, $statusBoleh, true) ? $status : 'Diterima',
                        'pid'     => $promoByJudul[$promo] ?? null,
                        'npromo'  => $promo,
                        'diskon'  => min(100, max(0, (int)$diskon)),
                        'tanggal' => $this->tanggalLama($tanggal),
                    ]
                );
                $idPesanan[$kode] = (int)$this->db->lastInsertId();
                $jumlah['pesanan']++;
            }

            // ----- ulasan -----
            foreach ($this->bacaTxt('ulasan.txt', 3) as [$kode, $rating, $komentar]) {
                if (!isset($idPesanan[$kode])) {
                    continue;
                }
                if ($this->satuBaris('SELECT id FROM ulasan WHERE pesanan_id = :p', ['p' => $idPesanan[$kode]])) {
                    continue;
                }
                $this->jalankan(
                    'INSERT INTO ulasan (pesanan_id, rating, komentar) VALUES (:p, :r, :k)',
                    ['p' => $idPesanan[$kode], 'r' => min(5, max(1, (int)$rating)), 'k' => $komentar]
                );
                $jumlah['ulasan']++;
            }

            $this->db->commit();
            return $jumlah;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack(); // tidak ada satu pun data yang tersimpan
            }
            throw $e;
        }
    }

    private function namaUser(int $id): string
    {
        $u = $this->satuBaris('SELECT nama FROM users WHERE id = :id', ['id' => $id]);
        return $u ? $u['nama'] : '';
    }
}
