<?php

namespace Tests\Feature;

use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HakAksesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $pelanggan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('role', 'admin')->first();
        $this->pelanggan = User::where('role', 'customer')->first();
    }

    public function test_tamu_tidak_bisa_membuka_halaman_dalam(): void
    {
        foreach (['dashboard', 'transaksi.index', 'pesanan.index', 'profil', 'log.index'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    public function test_pelanggan_tidak_bisa_membuka_halaman_admin(): void
    {
        foreach (['transaksi.index', 'layanan.index', 'promo.index', 'pelanggan.index', 'karyawan.index', 'ulasan.index', 'log.index', 'transaksi.export'] as $route) {
            $this->actingAs($this->pelanggan)->get(route($route))->assertRedirect(route('dashboard'));
        }
    }

    public function test_admin_bisa_membuka_semua_halaman_admin(): void
    {
        foreach (['dashboard', 'transaksi.index', 'layanan.index', 'promo.index', 'pelanggan.index', 'karyawan.index', 'ulasan.index', 'log.index', 'profil'] as $route) {
            $this->actingAs($this->admin)->get(route($route))->assertOk();
        }
    }

    public function test_pelanggan_bisa_membuka_halaman_pelanggan(): void
    {
        foreach (['dashboard', 'pesanan.index', 'riwayat.index', 'profil'] as $route) {
            $this->actingAs($this->pelanggan)->get(route($route))->assertOk();
        }
    }

    public function test_pelanggan_tidak_bisa_melihat_nota_milik_orang_lain(): void
    {
        $lain = User::create(['nama' => 'Lain', 'username' => 'lain@contoh.com', 'password' => bcrypt('rahasia1'), 'role' => 'customer']);
        $layanan = Layanan::first();
        $pesanan = Pesanan::create([
            'kode' => Pesanan::buatKode(), 'user_id' => $lain->id, 'nama_pelanggan' => 'Lain', 'sumber' => 'aplikasi',
            'layanan_id' => $layanan->id, 'nama_layanan' => $layanan->nama, 'harga' => $layanan->harga,
            'satuan' => $layanan->satuan, 'berat' => 2, 'catatan' => 'Jl. A', 'status' => 'Diterima',
            'nama_promo' => '', 'diskon' => 0, 'tanggal' => now(),
        ]);

        $this->actingAs($this->pelanggan)->get(route('nota', $pesanan))->assertNotFound();
        $this->actingAs($lain)->get(route('nota', $pesanan))->assertOk()->assertSee($pesanan->kode);
        $this->actingAs($this->admin)->get(route('nota', $pesanan))->assertOk();
    }
}
