<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Menghubungkan akun staff dengan data karyawan.
// Setiap akun staff (role admin) adalah seorang karyawan,
// sedangkan akun pelanggan tidak terhubung ke karyawan.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('karyawan_id')
                ->nullable()
                ->unique()                  // satu karyawan paling banyak satu akun
                ->after('role')
                ->constrained('karyawan')
                ->restrictOnDelete();       // karyawan yang masih punya akun tidak boleh dihapus
        });

        // Akun staff lama dibuatkan data karyawan supaya aturan di bawah terpenuhi.
        // No. HP dikosongkan dulu, nanti dilengkapi lewat menu Karyawan.
        $staffTanpaKaryawan = DB::table('users')->where('role', 'admin')->whereNull('karyawan_id')->get();
        foreach ($staffTanpaKaryawan as $staff) {
            $idKaryawan = DB::table('karyawan')->insertGetId([
                'nama'    => $staff->nama,
                'jabatan' => 'Admin Toko',
                'no_hp'   => '',
            ]);
            DB::table('users')->where('id', $staff->id)->update(['karyawan_id' => $idKaryawan]);
        }

        // Staff wajib terhubung ke karyawan, pelanggan tidak boleh
        DB::statement("ALTER TABLE users ADD CONSTRAINT chk_users_karyawan CHECK (
            (role = 'admin' AND karyawan_id IS NOT NULL) OR (role = 'customer' AND karyawan_id IS NULL)
        )");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT chk_users_karyawan');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('karyawan_id');
        });
    }
};
