<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->integer('harga');
            $table->string('satuan', 20);
        });

        DB::statement('ALTER TABLE layanan ADD CONSTRAINT chk_layanan_harga CHECK (harga > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan');
    }
};
