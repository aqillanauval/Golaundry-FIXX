<?php
// ============================================================
// database.contoh.php  ->  CONTOH pengaturan database
//
// Cara pakai:
//   1. Copy file ini, lalu kasih nama  database.php  (di folder yang sama)
//   2. Isi pakai data dari Supabase kamu
//
// File database.php sengaja di-ignore sama git (lihat .gitignore),
// jadi password kamu nggak ikut ke-upload ke GitHub.
//
// Datanya dapet dari mana?
//   Supabase Dashboard > klik tombol "Connect" di atas > pilih "Session pooler"
//   Nanti muncul tulisan kayak gini:
//   postgresql://postgres.abcdefghijkl:[YOUR-PASSWORD]@aws-1-ap-southeast-1.pooler.supabase.com:5432/postgres
//                \_______ user ______/                \_______________ host ________________/ \port/ \nama db/
// ============================================================

$jenisDb = 'pgsql';   // 'pgsql' = Supabase (PostgreSQL), 'mysql' = MySQL di XAMPP

$host    = 'aws-1-ap-southeast-1.pooler.supabase.com';
$port    = '5432';
$namaDb  = 'postgres';
$user    = 'postgres.ISI_PROJECT_REF';     // pakai titik + kode project, bukan "postgres" doang
$pass    = 'ISI_PASSWORD_DATABASE';         // password database waktu bikin project (bukan API key)
$sslmode = 'require';                       // Supabase wajib pakai SSL

// --- Kalau mau balik pakai MySQL XAMPP, ganti jadi: ---
// $jenisDb = 'mysql';
// $host = '127.0.0.1';
// $port = '3306';
// $namaDb = 'golaundry';
// $user = 'root';
// $pass = '';
