<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100)->unique(); 
            $table->string('password', 255);
            $table->string('nama', 100);
            $table->string('role', 10)->default('customer');
            $table->timestamp('created_at')->useCurrent();
        });

        DB::statement("ALTER TABLE users ADD CONSTRAINT chk_users_role CHECK (role IN ('admin', 'customer'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
