<?php

namespace Modules\BankSampah\Tests\Unit\Models;

use Modules\BankSampah\Tests\TestCase;

class BankSampahTransaksiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createBankSampahTables();
    }

    public function test_table_name(): void
    {
        $model = new \Modules\BankSampah\Models\BankSampahTransaksi();
        $this->assertEquals('bank_sampah_transaksi', $model->getTable());
    }

    public function test_fillable_attributes(): void
    {
        $model = new \Modules\BankSampah\Models\BankSampahTransaksi();
        $fillable = $model->getFillable();
        
        $this->assertContains('config_id', $fillable);
        $this->assertContains('no_transaksi', $fillable);
        $this->assertContains('member_id', $fillable);
        $this->assertContains('tipe', $fillable);
        $this->assertContains('total', $fillable);
    }

    public function test_generate_no_transaksi_format(): void
    {
        $noTransaksi = \Modules\BankSampah\Models\BankSampahTransaksi::generateNoTransaksi();
        
        // Format: TRX-YYYYMMDD-XXXX
        $this->assertMatchesRegularExpression('/^TRX-\d{8}-\d{4}$/', $noTransaksi);
    }

    public function test_generate_no_transaksi_increments(): void
    {
        // Create existing transaksi
        $this->createTransaksi([
            'no_transaksi' => 'TRX-' . date('Ymd') . '-0001'
        ]);
        
        // Generate new number
        $noTransaksi = \Modules\BankSampah\Models\BankSampahTransaksi::generateNoTransaksi();
        
        // Should be incremented
        $this->assertStringEndsWith('-0002', $noTransaksi);
    }

    public function test_generate_no_transaksi_different_dates(): void
    {
        // Create transaksi for yesterday
        $yesterday = date('Ymd', strtotime('-1 day'));
        $this->createTransaksi([
            'no_transaksi' => "TRX-{$yesterday}-0005"
        ]);
        
        // Generate new number for today
        $noTransaksi = \Modules\BankSampah\Models\BankSampahTransaksi::generateNoTransaksi();
        
        // Should start from 0001 for new day
        $this->assertStringEndsWith('-0001', $noTransaksi);
    }

    public function test_member_relationship(): void
    {
        $transaksi = new \Modules\BankSampah\Models\BankSampahTransaksi();
        $relation = $transaksi->member();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    }

    public function test_lokasi_relationship(): void
    {
        $transaksi = new \Modules\BankSampah\Models\BankSampahTransaksi();
        $relation = $transaksi->lokasi();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    }

    public function test_detail_relationship(): void
    {
        $transaksi = new \Modules\BankSampah\Models\BankSampahTransaksi();
        $relation = $transaksi->detail();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    }
}
