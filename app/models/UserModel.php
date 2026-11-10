<?php
// ============================================================
// UserModel.php  ->  semua urusan tabel users
// Dipakai buat: Registrasi, Login, Ubah Profil, Hapus Akun
// Password disimpan pakai password_hash(), dicek pakai password_verify()
// ============================================================

class UserModel extends Model
{
    protected $tabel = 'users';

    // ubah 1 baris dari database jadi objek Admin atau Pelanggan
    private function jadiObjek($data)
    {
        if ($data['role'] == 'admin') {
            return new Admin($data);
        }
        return new Pelanggan($data);
    }

    public function cariById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => (int)$id]); // (int) = paksa jadi angka
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }
        return $this->jadiObjek($data);
    }

    // dipakai waktu login. Pakai prepared statement biar aman dari SQL injection
    public function cariByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }
        return $this->jadiObjek($data);
    }

    public function semuaPelanggan()
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE role = 'customer' ORDER BY nama");
        $hasil = [];
        foreach ($stmt->fetchAll() as $data) {
            $hasil[] = $this->jadiObjek($data);
        }
        return $hasil;
    }

    public function jumlahPelanggan()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
        return (int)$stmt->fetchColumn(); // fetchColumn() = ambil 1 nilai aja
    }

    // tambah user baru. Hasilnya id user baru, atau 0 kalau username udah dipakai
    public function tambah($username, $password, $nama, $role = 'customer')
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO users (username, password, nama, role) VALUES (:username, :password, :nama, :role)"
            );
            $stmt->execute([
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT), // JANGAN simpan password asli
                'nama'     => $nama,
                'role'     => $role,
            ]);
            return (int)$this->db->lastInsertId(); // id dari baris yang baru masuk
        } catch (PDOException $e) {
            // kode 23000 (MySQL) / 23505 (PostgreSQL) = data kembar di kolom UNIQUE
            if ($e->getCode() == '23000' || $e->getCode() == '23505') {
                return 0;
            }
            throw $e; // error lain dilempar lagi
        }
    }

    public function ubahNama($id, $nama)
    {
        $stmt = $this->db->prepare("UPDATE users SET nama = :nama WHERE id = :id");
        $stmt->execute(['nama' => $nama, 'id' => (int)$id]);
    }

    public function ubahPassword($id, $passwordBaru)
    {
        $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute([
            'password' => password_hash($passwordBaru, PASSWORD_DEFAULT),
            'id'       => (int)$id,
        ]);
    }

    // ------------------------------------------------------------
    // HAPUS AKUN PELANGGAN  ->  pakai TRANSAKSI
    //   Langkah 1: pesanan yang masih "Menunggu Jemput" dibatalin (dihapus)
    //   Langkah 2: akunnya dihapus
    //              (pesanan lama tetap ada sebagai riwayat, user_id-nya jadi NULL)
    // Dua langkah ini harus berhasil BARENGAN.
    // Kalau salah satu gagal -> rollBack(), semua dibatalin, data balik kayak semula.
    // Akun admin aman karena ada syarat  role = 'customer'.
    // ------------------------------------------------------------
    public function hapusAkunPelanggan($id)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("DELETE FROM pesanan WHERE user_id = :id AND status = 'Menunggu Jemput'");
            $stmt->execute(['id' => (int)$id]); // (int) = paksa jadi angka

            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id AND role = 'customer'");
            $stmt->execute(['id' => (int)$id]); // (int) = paksa jadi angka
            if ($stmt->rowCount() != 1) {
                throw new Exception('Akun pelanggan nggak ketemu.');
            }

            $this->db->commit(); // simpan semua perubahan
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack(); // batalin semua perubahan
            }
            error_log('Hapus akun gagal: ' . $e->getMessage());
            return false;
        }
    }
}
