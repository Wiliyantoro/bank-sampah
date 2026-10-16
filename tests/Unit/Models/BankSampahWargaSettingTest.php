<?php

namespace Modules\BankSampah\Tests\Unit\Models;

use Modules\BankSampah\Tests\TestCase;

class BankSampahWargaSettingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createBankSampahTables();
    }

    public function test_get_settings_creates_default_row(): void
    {
        $settings = \Modules\BankSampah\Models\BankSampahWargaSetting::getSettings($this->configId);

        $this->assertEquals($this->configId, $settings->config_id);
        $this->assertTrue((bool) $settings->warga_aktif);
        $this->assertTrue((bool) $settings->require_pin);
        $this->assertSame(10, $settings->max_riwayat);
    }

    public function test_get_settings_is_idempotent_per_config(): void
    {
        // BS-027: firstOrCreate berulang harus mengembalikan baris yang sama
        $first  = \Modules\BankSampah\Models\BankSampahWargaSetting::getSettings($this->configId);
        $second = \Modules\BankSampah\Models\BankSampahWargaSetting::getSettings($this->configId);

        $this->assertSame($first->id, $second->id);

        $count = \DB::table('bank_sampah_warga_settings')
            ->where('config_id', $this->configId)
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_unique_config_id_blocks_duplicate_rows(): void
    {
        // BS-027: UNIQUE(config_id) menolak baris kedua untuk desa yang sama
        \Modules\BankSampah\Models\BankSampahWargaSetting::getSettings($this->configId);

        $this->expectException(\Illuminate\Database\QueryException::class);

        \DB::table('bank_sampah_warga_settings')->insert([
            'config_id'    => $this->configId,
            'warga_aktif'  => 1,
            'require_pin'  => 0,
        ]);
    }

    public function test_get_settings_returns_same_row_after_exception_path(): void
    {
        // BS-027: jalur catch QueryException di getSettings() membaca ulang
        // baris yang sudah ada — pemanggilan berulang tetap baris yang sama
        $first = \Modules\BankSampah\Models\BankSampahWargaSetting::getSettings($this->configId);
        $again = \Modules\BankSampah\Models\BankSampahWargaSetting::getSettings($this->configId);

        $this->assertSame($first->id, $again->id);
    }

    public function test_settings_isolated_per_desa(): void
    {
        $settingsA = \Modules\BankSampah\Models\BankSampahWargaSetting::getSettings($this->configId);
        $settingsB = \Modules\BankSampah\Models\BankSampahWargaSetting::getSettings(999999);

        $this->assertNotSame($settingsA->id, $settingsB->id);
        $this->assertEquals(999999, $settingsB->config_id);
    }
}
