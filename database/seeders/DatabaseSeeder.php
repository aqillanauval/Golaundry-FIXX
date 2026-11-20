<?php

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\Promo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Data awal, sama dengan isi INSERT di database/mysql.sql proyek lama.
// Jalankan: php artisan db:seed
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // firstOrCreate = buat kalau belum ada, jadi aman dijalankan berkali-kali
        User::firstOrCreate(
            ['username' => 'admin@golaundry.com'],
            ['password' => Hash::make('admin123'), 'nama' => 'Admin GoLaundry', 'role' => 'admin']
        );
        User::firstOrCreate(
            ['username' => 'pelanggan@golaundry.com'],
            ['password' => Hash::make('pelanggan123'), 'nama' => 'Pelanggan GoLaundry', 'role' => 'customer']
        );

        if (Layanan::count() == 0) {
            Layanan::create(['nama' => 'Cuci Kering', 'harga' => 7000, 'satuan' => 'kg']);
            Layanan::create(['nama' => 'Cuci Setrika', 'harga' => 10000, 'satuan' => 'kg']);
            Layanan::create(['nama' => 'Dry Clean', 'harga' => 15000, 'satuan' => 'item']);
            Layanan::create(['nama' => 'Cuci Express', 'harga' => 15000, 'satuan' => 'kg']);
        }

        if (Promo::count() == 0) {
            Promo::create([
                'judul' => 'Diskon Mahasiswa',
                'deskripsi' => 'Tunjukkan KTM dan dapatkan potongan harga untuk semua layanan cuci kiloan.',
                'diskon' => 15,
                'berlaku_sampai' => '2026-12-31',
                'foto' => '',
            ]);
            Promo::create([
                'judul' => 'Cuci Express Hemat',
                'deskripsi' => 'Butuh cepat? Layanan Cuci Express lebih hemat di akhir pekan.',
                'diskon' => 10,
                'berlaku_sampai' => '2026-11-30',
                'foto' => '',
            ]);
        }
    }
}
