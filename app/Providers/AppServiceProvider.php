<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Muat fungsi bantu (rupiah, formatBerat, tanggalIndo, dll.)
        require_once app_path('helpers.php');
    }

    public function boot(): void
    {
        //
    }
}
