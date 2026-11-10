<?php
// ============================================================
// UserModel.php  -  Akses tabel users (Registrasi, Login, Ubah Profil, Hapus Akun)
// Password disimpan dengan password_hash() dan dicek dengan password_verify().
// ============================================================

class UserModel extends Model
{
    protected string $tabel = 'users';

    public function cariById(int $id): ?Pengguna
    {
        $baris = $this->satuBaris('SELECT * FROM users WHERE id = :id', ['id' => $id]);
        return $baris ? Pengguna::dariBaris($baris) : null;
    }

    // dipakai saat login: WHERE username = :username (prepared statement)
    public function cariByUsername(string $username): ?Pengguna
    {
        $baris = $this->satuBaris('SELECT * FROM users WHERE username = :username', ['username' => $username]);
        return $baris ? Pengguna::dariBaris($baris) : null;
    }

    public function usernameSudahAda(string $username): bool
    {
        return $this->satuBaris('SELECT id FROM users WHERE username = :u', ['u' => $username]) !== null;
    }

    /** @return Pelanggan[] */
    public function daftarPelanggan(): array
    {
        $hasil = [];
        foreach ($this->semuaBaris("SELECT * FROM users WHERE role = 'customer' ORDER BY nama") as $b) {
            $hasil[] = Pengguna::dariBaris($b);
        }
        return $hasil;
    }

    public function jumlahPelanggan(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    }

    // INSERT. Mengembalikan id baru, atau 0 kalau username sudah terdaftar.
    public function tambah(string $username, string $passwordPolos, string $nama, string $role = 'customer'): int
    {
        try {
            $this->jalankan(
                'INSERT INTO users (username, password, nama, role) VALUES (:username, :password, :nama, :role)',
                [
                    'username' => $username,
                    'password' => password_hash($passwordPolos, PASSWORD_DEFAULT),
                    'nama'     => $nama,
                    'role'     => $role,
                ]
            );
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            if (Database::isDuplikat($e)) {
                return 0; // username kembar (kolom UNIQUE)
            }
            throw $e;
        }
    }

    public function ubahNama(int $id, string $nama): void
    {
        $this->jalankan('UPDATE users SET nama = :nama WHERE id = :id', ['nama' => $nama, 'id' => $id]);
    }

    public function ubahPassword(int $id, string $passwordPolos): void
    {
        $this->jalankan(
            'UPDATE users SET password = :password WHERE id = :id',
            ['password' => password_hash($passwordPolos, PASSWORD_DEFAULT), 'id' => $id]
        );
    }

    // ------------------------------------------------------------
    // TRANSAKSI: hapus akun pelanggan.
    //   1. pesanan yang masih "Menunggu Jemput" dibatalkan (dihapus)
    //   2. akunnya dihapus. Pesanan lama tetap ada sebagai riwayat
    //      (user_id otomatis jadi NULL karena ON DELETE SET NULL)
    // Kedua langkah harus berhasil bersama. Kalau salah satu gagal -> rollBack().
    // Dipakai admin (menu Pelanggan) dan pelanggan sendiri (Profil > Hapus akun).
    // Akun admin tidak bisa terhapus karena ada syarat WHERE role = 'customer'.
    // ------------------------------------------------------------
    public function hapusAkunPelanggan(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            $this->jalankan(
                "DELETE FROM pesanan WHERE user_id = :id AND status = 'Menunggu Jemput'",
                ['id' => $id]
            );
            $hapus = $this->jalankan("DELETE FROM users WHERE id = :id AND role = 'customer'", ['id' => $id]);
            if ($hapus->rowCount() !== 1) {
                throw new RuntimeException('Akun pelanggan tidak ditemukan.');
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack(); // batalkan semua perubahan
            }
            error_log('Hapus akun gagal: ' . $e->getMessage());
            return false;
        }
    }
}
