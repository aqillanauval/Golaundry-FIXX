<?php

namespace Tests\Feature;

use App\Helpers\LogAktivitas;
use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiturAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('role', 'admin')->first();
        LogAktivitas::kosongkan();
    }

    public function test_crud_layanan_dan_validasinya(): void
    {
        $this->actingAs($this->admin)
            ->post(route('layanan.store'), ['nama' => 'Cuci Sepatu', 'harga' => 25000, 'satuan' => 'pasang'])
            ->assertRedirect(route('layanan.index'));
        $layanan = Layanan::where('nama', 'Cuci Sepatu')->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('layanan.store'), ['nama' => 'Salah', 'harga' => 0, 'satuan' => 'liter'])
            ->assertSessionHasErrors(['harga', 'satuan']);

        $this->actingAs($this->admin)
            ->put(route('layanan.update', $layanan), ['nama' => 'Cuci Sepatu Premium', 'harga' => 30000, 'satuan' => 'pasang']);
        $this->assertDatabaseHas('layanan', ['id' => $layanan->id, 'harga' => 30000]);

        $this->actingAs($this->admin)->delete(route('layanan.destroy', $layanan));
        $this->assertDatabaseMissing('layanan', ['id' => $layanan->id]);
    }

    public function test_promo_baru_tidak_boleh_bertanggal_lampau(): void
    {
        $this->actingAs($this->admin)->post(route('promo.store'), [
            'judul' => 'Kemarin', 'deskripsi' => 'x', 'diskon' => 10, 'berlaku_sampai' => now()->subDay()->format('Y-m-d'),
        ])->assertSessionHasErrors('berlaku_sampai');
    }

    public function test_transaksi_kios_dan_export_csv(): void
    {
        $layanan = Layanan::where('nama', 'Cuci Kering')->first();
        $this->actingAs($this->admin)->post(route('transaksi.store'), [
            'nama_pelanggan' => 'Pak Kios', 'layanan_id' => $layanan->id, 'berat' => 2.5, 'status' => 'Dicuci',
        ])->assertRedirect(route('transaksi.index'));

        $pesanan = Pesanan::where('nama_pelanggan', 'Pak Kios')->firstOrFail();
        $this->assertSame('kios', $pesanan->sumber);
        $this->assertSame(17500, $pesanan->total());

        $csv = $this->actingAs($this->admin)->get(route('transaksi.export', ['cari' => 'Pak Kios']));
        $csv->assertOk();
        $csv->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $isi = $csv->streamedContent();
        $this->assertStringContainsString('No. Nota', $isi);
        $this->assertStringContainsString($pesanan->kode, $isi);
        $this->assertStringContainsString('17500', $isi);
    }

    public function test_aktivitas_tercatat_di_log(): void
    {
        $this->actingAs($this->admin)
            ->post(route('layanan.store'), ['nama' => 'Setrika Saja', 'harga' => 5000, 'satuan' => 'kg']);

        $this->actingAs($this->admin)->get(route('log.index'))
            ->assertOk()
            ->assertSee('Tambah layanan')
            ->assertSee('Setrika Saja');

        $this->actingAs($this->admin)->delete(route('log.destroy'))->assertRedirect(route('log.index'));
        $this->assertCount(1, LogAktivitas::baca()); // tinggal catatan "Kosongkan log aktivitas"
    }

    public function test_pelanggan_tidak_bisa_dihapus_lewat_route_admin_jika_dia_staff(): void
    {
        $this->actingAs($this->admin)->delete(route('pelanggan.destroy', $this->admin))->assertNotFound();
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }
}
