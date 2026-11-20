<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 100);
            $table->string('deskripsi', 500);
            $table->smallInteger('diskon');         
            $table->date('berlaku_sampai');
            $table->string('foto', 255)->default(''); 
        });

        DB::statement('ALTER TABLE promo ADD CONSTRAINT chk_promo_diskon CHECK (diskon BETWEEN 1 AND 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('promo');
    }
};
