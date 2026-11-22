<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Menambahkan relasi KURIR menjemput PESANAN.
// kurir_id boleh kosong: pesanan kios tidak dijemput, dan pesanan aplikasi
// baru diisi kurirnya setelah cucian dijemput dan ditimbang.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->foreignId('kurir_id')
                ->nullable()
                ->after('status')
                ->constrained('karyawan')
                ->nullOnDelete(); // karyawan dihapus, riwayat pesanan tetap ada
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kurir_id');
        });
    }
};
