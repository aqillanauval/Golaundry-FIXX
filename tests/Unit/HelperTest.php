<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/helpers.php';

class HelperTest extends TestCase
{
    public function test_format_rupiah(): void
    {
        $this->assertSame('Rp 0', rupiah(0));
        $this->assertSame('Rp 1.250.000', rupiah(1250000));
    }

    public function test_format_berat(): void
    {
        $this->assertSame('3', formatBerat(3));
        $this->assertSame('2,5', formatBerat(2.5));
        $this->assertSame('1,3', formatBerat(1.26));
    }

    public function test_validasi_username(): void
    {
        $this->assertTrue(usernameValid('budi@contoh.com'));
        $this->assertTrue(usernameValid('081234567890'));
        $this->assertTrue(usernameValid('+6281234567890'));
        $this->assertFalse(usernameValid('budi'));
        $this->assertFalse(usernameValid('0812'));
    }

    public function test_normalisasi_username(): void
    {
        $this->assertSame('budi@contoh.com', normalUser('  Budi@Contoh.COM '));
        $this->assertSame('0812345678', normalUser(' 0812345678 '));
    }
}
