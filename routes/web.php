<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UlasanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['guest', 'tanpa.cache'])->group(function () {
    Route::get('/login', [AuthController::class, 'formLogin'])->name('login');
    // maksimal 5 percobaan login per menit untuk mencegah tebak-tebakan password (brute force)
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.proses');
    Route::get('/registrasi', [AuthController::class, 'formRegister'])->name('register');
    Route::post('/registrasi', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.proses');
});

Route::middleware(['auth', 'tanpa.cache'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'index'])->name('profil');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profil.destroy');

    Route::get('/nota/{pesanan}', [NotaController::class, 'show'])->name('nota');

    Route::middleware('role:admin')->group(function () {
        Route::get('/layanan', [LayananController::class, 'index'])->name('layanan.index');
        Route::post('/layanan', [LayananController::class, 'store'])->name('layanan.store');
        Route::get('/layanan/{layanan}/edit', [LayananController::class, 'edit'])->name('layanan.edit');
        Route::put('/layanan/{layanan}', [LayananController::class, 'update'])->name('layanan.update');
        Route::delete('/layanan/{layanan}', [LayananController::class, 'destroy'])->name('layanan.destroy');

        Route::get('/promo', [PromoController::class, 'index'])->name('promo.index');
        Route::post('/promo', [PromoController::class, 'store'])->name('promo.store');
        Route::get('/promo/{promo}/edit', [PromoController::class, 'edit'])->name('promo.edit');
        Route::put('/promo/{promo}', [PromoController::class, 'update'])->name('promo.update');
        Route::delete('/promo/{promo}', [PromoController::class, 'destroy'])->name('promo.destroy');

        Route::get('/karyawan', [KaryawanController::class, 'index'])->name('karyawan.index');
        Route::post('/karyawan', [KaryawanController::class, 'store'])->name('karyawan.store');
        Route::get('/karyawan/{karyawan}/edit', [KaryawanController::class, 'edit'])->name('karyawan.edit');
        Route::put('/karyawan/{karyawan}', [KaryawanController::class, 'update'])->name('karyawan.update');
        Route::delete('/karyawan/{karyawan}', [KaryawanController::class, 'destroy'])->name('karyawan.destroy');

        Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan.index');
        Route::post('/pelanggan', [PelangganController::class, 'store'])->name('pelanggan.store');
        Route::get('/pelanggan/{pelanggan}/edit', [PelangganController::class, 'edit'])->name('pelanggan.edit');
        Route::put('/pelanggan/{pelanggan}', [PelangganController::class, 'update'])->name('pelanggan.update');
        Route::delete('/pelanggan/{pelanggan}', [PelangganController::class, 'destroy'])->name('pelanggan.destroy');

        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/transaksi/export', [TransaksiController::class, 'export'])->name('transaksi.export');
        Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
        Route::get('/transaksi/{pesanan}/edit', [TransaksiController::class, 'edit'])->name('transaksi.edit');
        Route::put('/transaksi/{pesanan}', [TransaksiController::class, 'update'])->name('transaksi.update');
        Route::delete('/transaksi/{pesanan}', [TransaksiController::class, 'destroy'])->name('transaksi.destroy');
        Route::post('/transaksi/{pesanan}/berat', [TransaksiController::class, 'isiBerat'])->name('transaksi.berat');
        Route::post('/transaksi/{pesanan}/status', [TransaksiController::class, 'ubahStatus'])->name('transaksi.status');

        Route::get('/ulasan', [UlasanController::class, 'index'])->name('ulasan.index');
        Route::delete('/ulasan/{ulasan}', [UlasanController::class, 'destroy'])->name('ulasan.destroy');

        Route::get('/log', [LogController::class, 'index'])->name('log.index');
        Route::delete('/log', [LogController::class, 'destroy'])->name('log.destroy');
    });

    Route::middleware('role:customer')->group(function () {
        Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan.index');
        Route::post('/pesanan', [PesananController::class, 'store'])->name('pesanan.store');
        Route::get('/pesanan/{pesanan}/edit', [PesananController::class, 'edit'])->name('pesanan.edit');
        Route::put('/pesanan/{pesanan}', [PesananController::class, 'update'])->name('pesanan.update');
        Route::delete('/pesanan/{pesanan}', [PesananController::class, 'destroy'])->name('pesanan.destroy');

        Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');
        Route::post('/riwayat/{pesanan}/ulasan', [RiwayatController::class, 'simpanUlasan'])->name('riwayat.ulasan');
        Route::delete('/riwayat/{pesanan}/ulasan', [RiwayatController::class, 'hapusUlasan'])->name('riwayat.ulasan.hapus');
    });
});
