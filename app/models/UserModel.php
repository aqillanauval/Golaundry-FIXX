<?php
class UserModel extends Model
{
    protected $tabel = 'users';
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
        $stmt->execute(['id' => (int)$id]); 
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }
        return $this->jadiObjek($data);
    }

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
        return (int)$stmt->fetchColumn();
    }

    public function tambah($username, $password, $nama, $role = 'customer')
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO users (username, password, nama, role) VALUES (:username, :password, :nama, :role)"
            );
            $stmt->execute([
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT), 
                'nama'     => $nama,
                'role'     => $role,
            ]);
            return (int)$this->db->lastInsertId(); 
        } catch (PDOException $e) {
            if ($e->getCode() == '23000' || $e->getCode() == '23505') {
                return 0;
            }
            throw $e; 
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

    public function hapusAkunPelanggan($id)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("DELETE FROM pesanan WHERE user_id = :id AND status = 'Menunggu Jemput'");
            $stmt->execute(['id' => (int)$id]); 

            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id AND role = 'customer'");
            $stmt->execute(['id' => (int)$id]); 
            if ($stmt->rowCount() != 1) {
                throw new Exception('Akun pelanggan nggak ketemu.');
            }

            $this->db->commit(); 
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack(); 
            }
            error_log('Hapus akun gagal: ' . $e->getMessage());
            return false;
        }
    }
}
