<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_halaman_awal_diarahkan_ke_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk()->assertSee('GOLaundry', false);
    }

    public function test_login_berhasil_dengan_akun_benar(): void
    {
        $this->post(route('login.proses'), ['username' => 'admin@golaundry.com', 'password' => 'admin123'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_login_gagal_dengan_password_salah(): void
    {
        $this->post(route('login.proses'), ['username' => 'admin@golaundry.com', 'password' => 'salah'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_login_dibatasi_setelah_terlalu_banyak_percobaan(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.proses'), ['username' => 'admin@golaundry.com', 'password' => 'salah']);
        }

        $this->post(route('login.proses'), ['username' => 'admin@golaundry.com', 'password' => 'admin123'])
            ->assertStatus(429);
        $this->assertGuest();
    }

    public function test_registrasi_selalu_menjadi_pelanggan(): void
    {
        $this->post(route('register.proses'), [
            'nama' => 'Budi', 'username' => 'Budi@Contoh.com',
            'password' => 'rahasia1', 'password_confirmation' => 'rahasia1',
            'role' => 'admin', // dicoba kirim role admin, harus diabaikan
        ])->assertRedirect(route('login'));

        $user = User::where('username', 'budi@contoh.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('customer', $user->role);
        $this->assertNotSame('rahasia1', $user->password); // tersimpan dalam bentuk hash
    }

    public function test_registrasi_menolak_username_tidak_valid_dan_duplikat(): void
    {
        $this->post(route('register.proses'), [
            'nama' => 'X', 'username' => 'bukan-email', 'password' => 'rahasia1', 'password_confirmation' => 'rahasia1',
        ])->assertSessionHasErrors('username');

        $this->post(route('register.proses'), [
            'nama' => 'X', 'username' => 'pelanggan@golaundry.com', 'password' => 'rahasia1', 'password_confirmation' => 'rahasia1',
        ])->assertSessionHasErrors('username');
    }

    public function test_logout(): void
    {
        $this->actingAs(User::where('role', 'customer')->first())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
