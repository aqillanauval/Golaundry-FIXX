<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Catatan: CHECK constraint lewat ALTER TABLE hanya didukung MySQL/MariaDB,
    // jadi dilewati saat memakai SQLite (dipakai oleh php artisan test).
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_pelanggan', 100);
            $table->string('sumber', 10); 

            $table->foreignId('layanan_id')->nullable()->constrained('layanan')->nullOnDelete();
            $table->string('nama_layanan', 100);
            $table->integer('harga');
            $table->string('satuan', 20);

            $table->decimal('berat', 6, 1)->default(0);
            $table->string('catatan', 500)->default(''); 
            $table->string('status', 20)->default('Menunggu Jemput');

            $table->foreignId('promo_id')->nullable()->constrained('promo')->nullOnDelete();
            $table->string('nama_promo', 100)->default('');
            $table->smallInteger('diskon')->default(0);

            $table->dateTime('tanggal');

            $table->index('status');
            $table->index('tanggal');
        });

        DB::getDriverName() === 'sqlite' || DB::statement("ALTER TABLE pesanan ADD CONSTRAINT chk_pesanan_sumber CHECK (sumber IN ('aplikasi', 'kios'))");
        DB::getDriverName() === 'sqlite' || DB::statement('ALTER TABLE pesanan ADD CONSTRAINT chk_pesanan_harga CHECK (harga >= 0)');
        DB::getDriverName() === 'sqlite' || DB::statement('ALTER TABLE pesanan ADD CONSTRAINT chk_pesanan_berat CHECK (berat >= 0)');
        DB::getDriverName() === 'sqlite' || DB::statement('ALTER TABLE pesanan ADD CONSTRAINT chk_pesanan_diskon CHECK (diskon BETWEEN 0 AND 100)');
        DB::getDriverName() === 'sqlite' || DB::statement("ALTER TABLE pesanan ADD CONSTRAINT chk_pesanan_status CHECK (status IN ('Menunggu Jemput', 'Diterima', 'Dicuci', 'Selesai', 'Diambil'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
