<?php
// ============================================================
// Database.php  -  Satu-satunya tempat membuat koneksi PDO.
//
// Pola "singleton": koneksi dibuat sekali per request, lalu dipakai
// bersama oleh semua model lewat Database::koneksi().
//
// Materi yang dipakai:
//   - DSN berisi driver, host, port, nama database (dan charset / sslmode)
//   - ERRMODE_EXCEPTION : kesalahan SQL menjadi exception
//   - FETCH_ASSOC       : baris hasil berupa array asosiatif
//   - try ... catch + error_log(): detail error (termasuk user & password)
//     TIDAK ditampilkan ke pengguna, hanya dicatat di log server
// ============================================================

class Database
{
    private static ?PDO $pdo = null;

    // constructor private: objek Database tidak perlu dibuat dengan "new"
    private function __construct()
    {
    }

    public static function koneksi(): PDO
    {
        if (self::$pdo === null) {
            $c = require APP_PATH . '/config/database.php';

            if ($c['driver'] === 'pgsql') {
                // PostgreSQL / Supabase
                $dsn = "pgsql:host={$c['host']};port={$c['port']};dbname={$c['dbname']};sslmode={$c['sslmode']}";
            } else {
                // MySQL / MariaDB (XAMPP)
                $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['dbname']};charset=utf8mb4";
            }

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // prepared statement asli dari DBMS
                PDO::ATTR_TIMEOUT            => 10,    // jangan loading tanpa akhir
            ];

            try {
                self::$pdo = new PDO($dsn, $c['user'], $c['pass'], $options);
            } catch (PDOException $e) {
                error_log('Koneksi basis data gagal: ' . $e->getMessage()); // detail hanya di log
                http_response_code(500);
                die('Koneksi basis data gagal. Periksa pengaturan di app/config/database.php '
                  . 'dan pastikan ekstensi pdo_' . htmlspecialchars($c['driver']) . ' aktif di php.ini.');
            }
        }
        return self::$pdo;
    }

    public static function driver(): string
    {
        return self::koneksi()->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    // pelanggaran UNIQUE: MySQL memberi SQLSTATE 23000, PostgreSQL 23505
    public static function isDuplikat(PDOException $e): bool
    {
        return in_array((string)$e->getCode(), ['23000', '23505'], true);
    }
}
