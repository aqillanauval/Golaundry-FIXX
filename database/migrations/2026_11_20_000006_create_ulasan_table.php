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
        Schema::create('ulasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->unique()->constrained('pesanan')->cascadeOnDelete();
            $table->smallInteger('rating');
            $table->string('komentar', 500);
            $table->timestamp('created_at')->useCurrent();
        });

        DB::getDriverName() === 'sqlite' || DB::statement('ALTER TABLE ulasan ADD CONSTRAINT chk_ulasan_rating CHECK (rating BETWEEN 1 AND 5)');
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasan');
    }
};
