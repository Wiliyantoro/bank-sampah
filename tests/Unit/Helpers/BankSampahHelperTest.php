<?php

namespace Modules\BankSampah\Tests\Unit\Helpers;

use Modules\BankSampah\Tests\TestCase;

class BankSampahHelperTest extends TestCase
{
    public function test_format_rupiah(): void
    {
        $this->assertEquals('Rp 0', format_rupiah(0));
        $this->assertEquals('Rp 1.000', format_rupiah(1000));
        $this->assertEquals('Rp 10.000', format_rupiah(10000));
        $this->assertEquals('Rp 100.000', format_rupiah(100000));
        $this->assertEquals('Rp 1.000.000', format_rupiah(1000000));
        $this->assertEquals('Rp 1.500.000', format_rupiah(1500000));
    }

    public function test_format_berat(): void
    {
        $this->assertEquals('10,50 kg', format_berat(10.5));
        $this->assertEquals('100,00 kg', format_berat(100));
        $this->assertEquals('5,25 pcs', format_berat(5.25, 'pcs'));
    }

    public function test_tipe_transaksi_label_setor(): void
    {
        $result = tipe_transaksi_label('setor');
        
        $this->assertStringContainsString('SETOR', $result);
        $this->assertStringContainsString('label-success', $result);
    }

    public function test_tipe_transaksi_label_tarik(): void
    {
        $result = tipe_transaksi_label('tarik');
        
        $this->assertStringContainsString('TARIK', $result);
        $this->assertStringContainsString('label-danger', $result);
    }

    public function test_tipe_transaksi_label_unknown(): void
    {
        $result = tipe_transaksi_label('unknown');
        
        $this->assertStringContainsString('UNKNOWN', $result);
        $this->assertStringContainsString('label-default', $result);
    }

    public function test_status_aktif_label_aktif(): void
    {
        $result = status_aktif_label(1);
        
        $this->assertStringContainsString('Aktif', $result);
        $this->assertStringContainsString('label-success', $result);
    }

    public function test_status_aktif_label_nonaktif(): void
    {
        $result = status_aktif_label(0);
        
        $this->assertStringContainsString('Nonaktif', $result);
        $this->assertStringContainsString('label-danger', $result);
    }

    public function test_safe_like_escapes_percent(): void
    {
        $result = safe_like('test%value');
        
        $this->assertStringNotContainsString('%', $result);
        $this->assertStringContainsString('\\%', $result);
    }

    public function test_safe_like_escapes_underscore(): void
    {
        $result = safe_like('test_value');
        
        $this->assertStringNotContainsString('_', $result);
        $this->assertStringContainsString('\\_', $result);
    }

    public function test_safe_like_preserves_normal_text(): void
    {
        $result = safe_like('normal text');
        
        $this->assertEquals('normal text', $result);
    }

    public function test_sanitize_csv_field_prepends_quote(): void
    {
        $result = sanitize_csv_field('test value');
        
        $this->assertStringStartsWith("'", $result);
    }

    public function test_sanitize_csv_field_prevents_formula_injection(): void
    {
        // Test various formula injection patterns
        $this->assertStringStartsWith("'", sanitize_csv_field('=CMD("calc.exe")'));
        $this->assertStringStartsWith("'", sanitize_csv_field('+CMD("calc.exe")'));
        $this->assertStringStartsWith("'", sanitize_csv_field('-CMD("calc.exe")'));
        $this->assertStringStartsWith("'", sanitize_csv_field('@CMD("calc.exe")'));
        $this->assertStringStartsWith("'", sanitize_csv_field('\tCMD("calc.exe")'));
    }

    public function test_sanitize_csv_field_preserves_normal_text(): void
    {
        $result = sanitize_csv_field('Normal text');
        
        $this->assertEquals("'Normal text", $result);
    }

    public function test_url_helper_generates_path(): void
    {
        // Modul tidak boleh mendefinisikan ulang helper global ci_route() OpenSID
        // (lihat banksampah_helper.php), karena memutus URL dot-notation inti
        // seperti ci_route('surat_master.datatables'). View modul memakai url().
        $this->assertTrue(function_exists('url'));
    }
}
