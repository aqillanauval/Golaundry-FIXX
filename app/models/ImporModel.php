<?php
// ============================================================
// ImporModel.php  ->  mindahin data lama dari file .txt (folder data/) ke database
//
// Dua materi kepakai di sini:
//  1. Operasi File (Modul 9): fopen('r'), fgets(), explode(), file_exists(), filesize()
//  2. TRANSAKSI database: semua proses impor dibungkus 1 transaksi.
//     Kalau ada 1 baris aja yang gagal -> rollBack(), database balik kayak semula.
//     Jadi nggak bakal ada data yang masuk setengah-setengah.
//
// Data yang udah ada (username / nama layanan / judul promo / No. Nota sama) dilewatin,
// jadi tombol impor aman diklik lebih dari sekali.
// ============================================================

class ImporModel extends Model
{
    // nama file => jumlah kolom per baris
    private $daftarFile = [
        'users.txt'    => 4,  // username,password,nama,role
        'layanan.txt'  => 4,  // id,nama,harga,satuan
        'karyawan.txt' => 4,  // id,nama,jabatan,no_hp
        'promo.txt'    => 6,  // id,judul,deskripsi,diskon,sampai,foto
        'orders.txt'   => 11, // id,username,layanan,harga,satuan,qty,catatan,status,tanggal,diskon,promo
        'ulasan.txt'   => 3,  // id_pesanan,rating,komentar
    ];

    // baca file .txt baris per baris, tiap baris dipecah pakai explode(',')
    public function bacaTxt($namaFile, $jumlahKolom)
    {
        $hasil = [];
        $lokasi = FOLDER_DATA_LAMA . $namaFile;
        if (!file_exists($lokasi)) {
            return $hasil;
        }

        $file = fopen($lokasi, 'r');
        while (($baris = fgets($file)) !== false) {
            $baris = trim($baris);
            if ($baris == '') {
                continue; // lewatin baris kosong
            }
            $kolom = explode(',', $baris, $jumlahKolom);
            // orders.txt versi lama cuma 9 kolom, sisanya diisi kosong
            while (count($kolom) < $jumlahKolom) {
                $kolom[] = '';
            }
            $hasil[] = $kolom;
        }
        fclose($file);
        return $hasil;
    }

    // info tiap file buat ditampilin sebelum impor
    public function ringkasan()
    {
        $hasil = [];
        foreach ($this->daftarFile as $namaFile => $jumlahKolom) {
            $ada = file_exists(FOLDER_DATA_LAMA . $namaFile);
            $hasil[] = [
                'file'   => $namaFile,
                'ada'    => $ada,
                'ukuran' => $ada ? filesize(FOLDER_DATA_LAMA . $namaFile) : 0,
                'baris'  => count($this->bacaTxt($namaFile, $jumlahKolom)),
            ];
        }
        return $hasil;
    }

    // "26 Sep 2026 16:43" -> "2026-09-26 16:43:00"
    private function ubahTanggal($teks)
    {
        $bulan = ['Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'Mei' => 5, 'Jun' => 6,
                  'Jul' => 7, 'Agu' => 8, 'Sep' => 9, 'Okt' => 10, 'Nov' => 11, 'Des' => 12];
        $bagian = explode(' ', trim($teks)); // [26, Sep, 2026, 16:43]

        if (count($bagian) >= 3 && isset($bulan[$bagian[1]])) {
            $jam = isset($bagian[3]) ? $bagian[3] : '00:00';
            return sprintf('%04d-%02d-%02d %s:00', $bagian[2], $bulan[$bagian[1]], $bagian[0], $jam);
        }
        return date('Y-m-d H:i:s');
    }

    // cek ada data atau nggak (dipakai biar nggak dobel)
    private function sudahAda($sql, $param)
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($param);
        return $stmt->fetch();
    }

    // ------------------------------------------------------------
    // jalanin impor. Hasilnya jumlah data baru per tabel
    // ------------------------------------------------------------
    public function jalankan()
    {
        $jumlah = ['users' => 0, 'layanan' => 0, 'karyawan' => 0, 'promo' => 0, 'pesanan' => 0, 'ulasan' => 0];

        try {
            $this->db->beginTransaction();

            // ----- 1. users -----
            $idUser = []; // username => id
            foreach ($this->bacaTxt('users.txt', 4) as $k) {
                $username = normalUser($k[0]);
                $ada = $this->sudahAda("SELECT id FROM users WHERE username = :u", ['u' => $username]);
                if ($ada) {
                    $idUser[$username] = $ada['id'];
                    continue;
                }
                // kalau password lama masih teks biasa, di-hash dulu
                $password = $k[1];
                if (substr($password, 0, 4) != '$2y$') {
                    $password = password_hash($password, PASSWORD_DEFAULT);
                }
                $role = ($k[3] == 'admin') ? 'admin' : 'customer';

                $stmt = $this->db->prepare("INSERT INTO users (username, password, nama, role) VALUES (:u, :p, :n, :r)");
                $stmt->execute(['u' => $username, 'p' => $password, 'n' => $k[2], 'r' => $role]);
                $idUser[$username] = $this->db->lastInsertId();
                $jumlah['users']++;
            }

            // ----- 2. layanan (dicocokin dari nama) -----
            foreach ($this->bacaTxt('layanan.txt', 4) as $k) {
                if ($this->sudahAda("SELECT id FROM layanan WHERE nama = :n", ['n' => $k[1]])) {
                    continue;
                }
                $stmt = $this->db->prepare("INSERT INTO layanan (nama, harga, satuan) VALUES (:n, :h, :s)");
                $stmt->execute(['n' => $k[1], 'h' => max(1, (int)$k[2]), 's' => $k[3]]);
                $jumlah['layanan']++;
            }
            $layananByNama = [];
            foreach ($this->db->query("SELECT * FROM layanan")->fetchAll() as $l) {
                $layananByNama[$l['nama']] = $l['id'];
            }

            // ----- 3. karyawan -----
            foreach ($this->bacaTxt('karyawan.txt', 4) as $k) {
                if ($this->sudahAda("SELECT id FROM karyawan WHERE nama = :n AND no_hp = :h", ['n' => $k[1], 'h' => $k[3]])) {
                    continue;
                }
                $stmt = $this->db->prepare("INSERT INTO karyawan (nama, jabatan, no_hp) VALUES (:n, :j, :h)");
                $stmt->execute(['n' => $k[1], 'j' => $k[2], 'h' => $k[3]]);
                $jumlah['karyawan']++;
            }

            // ----- 4. promo (dicocokin dari judul) -----
            foreach ($this->bacaTxt('promo.txt', 6) as $k) {
                if ($this->sudahAda("SELECT id FROM promo WHERE judul = :j", ['j' => $k[1]])) {
                    continue;
                }
                $sampai = preg_match('/^\d{4}-\d{2}-\d{2}$/', $k[4]) ? $k[4] : date('Y-m-d');
                $stmt = $this->db->prepare(
                    "INSERT INTO promo (judul, deskripsi, diskon, berlaku_sampai, foto) VALUES (:j, :d, :k, :s, :f)"
                );
                $stmt->execute([
                    'j' => $k[1], 'd' => $k[2], 'k' => min(100, max(1, (int)$k[3])),
                    's' => $sampai, 'f' => basename($k[5]),
                ]);
                $jumlah['promo']++;
            }
            $promoByJudul = [];
            foreach ($this->db->query("SELECT id, judul FROM promo")->fetchAll() as $p) {
                $promoByJudul[$p['judul']] = $p['id'];
            }

            // ----- 5. pesanan (orders.txt) -----
            $idPesanan = []; // kode lama => id baru
            foreach ($this->bacaTxt('orders.txt', 11) as $k) {
                $kode = $k[0];
                $ada = $this->sudahAda("SELECT id FROM pesanan WHERE kode = :k", ['k' => $kode]);
                if ($ada) {
                    $idPesanan[$kode] = $ada['id'];
                    continue;
                }

                $username = normalUser($k[1]);
                $userId = isset($idUser[$username]) ? $idUser[$username] : null;
                $status = in_array($k[7], Pesanan::DAFTAR_STATUS) ? $k[7] : 'Diterima';

                $stmt = $this->db->prepare(
                    "INSERT INTO pesanan
                        (kode, user_id, nama_pelanggan, sumber, layanan_id, nama_layanan, harga, satuan,
                         berat, catatan, status, promo_id, nama_promo, diskon, tanggal)
                     VALUES (:kode, :uid, :np, :sumber, :lid, :nl, :harga, :satuan,
                             :berat, :catatan, :status, :pid, :npromo, :diskon, :tanggal)"
                );
                $stmt->execute([
                    'kode'    => substr($kode, 0, 20),
                    'uid'     => $userId,
                    'np'      => $k[1],
                    'sumber'  => $userId ? 'aplikasi' : 'kios', // username terdaftar = pesan lewat aplikasi
                    'lid'     => isset($layananByNama[$k[2]]) ? $layananByNama[$k[2]] : null,
                    'nl'      => $k[2],
                    'harga'   => max(0, (int)$k[3]),
                    'satuan'  => $k[4],
                    'berat'   => max(0, round((float)$k[5], 1)),
                    'catatan' => $k[6],
                    'status'  => $status,
                    'pid'     => isset($promoByJudul[$k[10]]) ? $promoByJudul[$k[10]] : null,
                    'npromo'  => $k[10],
                    'diskon'  => min(100, max(0, (int)$k[9])),
                    'tanggal' => $this->ubahTanggal($k[8]),
                ]);
                $idPesanan[$kode] = $this->db->lastInsertId();
                $jumlah['pesanan']++;
            }

            // ----- 6. ulasan -----
            foreach ($this->bacaTxt('ulasan.txt', 3) as $k) {
                if (!isset($idPesanan[$k[0]])) {
                    continue; // pesanannya nggak ada, lewatin
                }
                if ($this->sudahAda("SELECT id FROM ulasan WHERE pesanan_id = :p", ['p' => $idPesanan[$k[0]]])) {
                    continue;
                }
                $stmt = $this->db->prepare("INSERT INTO ulasan (pesanan_id, rating, komentar) VALUES (:p, :r, :k)");
                $stmt->execute(['p' => $idPesanan[$k[0]], 'r' => min(5, max(1, (int)$k[1])), 'k' => $k[2]]);
                $jumlah['ulasan']++;
            }

            $this->db->commit(); // semua lancar -> simpan
            return $jumlah;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack(); // ada yang gagal -> batalin SEMUA
            }
            throw $e; // dilempar ke controller biar bisa ditampilin pesan errornya
        }
    }
}
