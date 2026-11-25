<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\Promo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Menguji alur utama: pelanggan pesan -> staff isi berat -> ubah status -> pelanggan memberi ulasan
class AlurPesananTest extends TestCase
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

    private function buatPesanan(array $tambahan = []): Pesanan
    {
        $layanan = Layanan::where('nama', 'Cuci Setrika')->first();
        $this->actingAs($this->pelanggan)->post(route('pesanan.store'), array_merge([
            'layanan_id' => $layanan->id, 'catatan' => 'Jl. Merdeka No. 1',
        ], $tambahan))->assertRedirect(route('dashboard'));

        return Pesanan::where('user_id', $this->pelanggan->id)->latest('id')->first();
    }

    public function test_pelanggan_membuat_pesanan_dengan_status_menunggu_jemput(): void
    {
        $pesanan = $this->buatPesanan();

        $this->assertSame('Menunggu Jemput', $pesanan->status);
        $this->assertSame(10000, $pesanan->harga);
        $this->assertEquals(0, $pesanan->berat);
    }

    public function test_alamat_jemput_wajib_diisi(): void
    {
        $this->actingAs($this->pelanggan)
            ->post(route('pesanan.store'), ['layanan_id' => Layanan::first()->id, 'catatan' => ''])
            ->assertSessionHasErrors('catatan');
    }

    public function test_alur_lengkap_dengan_diskon_promo_dan_ulasan(): void
    {
        $promo = Promo::create([
            'judul' => 'Tes Promo', 'deskripsi' => 'Diskon tes', 'diskon' => 20,
            'berlaku_sampai' => now()->addWeek()->format('Y-m-d'), 'foto' => '',
        ]);
        $pesanan = $this->buatPesanan(['promo_id' => $promo->id]);
        $kurir = Karyawan::whereRaw('LOWER(jabatan) = ?', ['kurir'])->first();

        // staff menimbang: 3 kg x Rp10.000 = Rp30.000, diskon 20% = Rp24.000
        $this->actingAs($this->admin)
            ->post(route('transaksi.berat', $pesanan), ['berat' => 3, 'kurir_id' => $kurir->id])
            ->assertSessionHas('sukses');
        $pesanan->refresh();
        $this->assertSame('Diterima', $pesanan->status);
        $this->assertSame(24000, $pesanan->total());

        // pelanggan belum boleh mengulas sebelum selesai
        $this->actingAs($this->pelanggan)
            ->post(route('riwayat.ulasan', $pesanan), ['rating' => 5, 'komentar' => 'Mantap'])
            ->assertSessionHas('error');

        // pesanan yang sudah dijemput tidak bisa dibatalkan pelanggan
        $this->actingAs($this->pelanggan)->delete(route('pesanan.destroy', $pesanan));
        $this->assertDatabaseHas('pesanan', ['id' => $pesanan->id]);

        // staff mengubah status lewat AJAX
        $this->actingAs($this->admin)
            ->postJson(route('transaksi.status', $pesanan), ['status' => 'Selesai'])
            ->assertJson(['sukses' => true]);

        $this->actingAs($this->pelanggan)
            ->post(route('riwayat.ulasan', $pesanan), ['rating' => 5, 'komentar' => 'Wangi dan rapi'])
            ->assertRedirect(route('riwayat.index'));
        $this->assertDatabaseHas('ulasan', ['pesanan_id' => $pesanan->id, 'rating' => 5]);
    }

    public function test_rating_harus_1_sampai_5(): void
    {
        $pesanan = $this->buatPesanan();
        $pesanan->update(['berat' => 1, 'status' => 'Selesai']);

        $this->actingAs($this->pelanggan)
            ->post(route('riwayat.ulasan', $pesanan), ['rating' => 9, 'komentar' => 'x'])
            ->assertSessionHasErrors('rating');
    }

    public function test_pelanggan_bisa_membatalkan_pesanan_yang_belum_dijemput(): void
    {
        $pesanan = $this->buatPesanan();

        $this->actingAs($this->pelanggan)->delete(route('pesanan.destroy', $pesanan))
            ->assertRedirect(route('pesanan.index'));
        $this->assertDatabaseMissing('pesanan', ['id' => $pesanan->id]);
    }

    public function test_pelanggan_tidak_bisa_mengubah_pesanan_orang_lain(): void
    {
        $pesanan = $this->buatPesanan();
        $lain = User::create(['nama' => 'Lain', 'username' => 'lain@contoh.com', 'password' => bcrypt('rahasia1'), 'role' => 'customer']);

        $this->actingAs($lain)->delete(route('pesanan.destroy', $pesanan))->assertForbidden();
    }

    public function test_promo_kedaluwarsa_ditolak(): void
    {
        $promo = Promo::create([
            'judul' => 'Lama', 'deskripsi' => 'x', 'diskon' => 10,
            'berlaku_sampai' => now()->subDay()->format('Y-m-d'), 'foto' => '',
        ]);

        $this->actingAs($this->pelanggan)
            ->post(route('pesanan.store'), ['layanan_id' => Layanan::first()->id, 'catatan' => 'Jl. A', 'promo_id' => $promo->id])
            ->assertSessionHasErrors('promo_id');
    }
}
