<?php

namespace Modules\BankSampah\Tests\Unit\Models;

use Modules\BankSampah\Tests\TestCase;

class BankSampahSampahTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createBankSampahTables();
    }

    public function test_table_name(): void
    {
        $model = new \Modules\BankSampah\Models\BankSampahSampah();
        $this->assertEquals('bank_sampah_sampah', $model->getTable());
    }

    public function test_fillable_attributes(): void
    {
        $model = new \Modules\BankSampah\Models\BankSampahSampah();
        $fillable = $model->getFillable();
        
        $this->assertContains('config_id', $fillable);
        $this->assertContains('kategori_id', $fillable);
        $this->assertContains('nama', $fillable);
        $this->assertContains('satuan', $fillable);
        $this->assertContains('harga_beli', $fillable);
        $this->assertContains('harga_jual', $fillable);
        $this->assertContains('stok', $fillable);
        $this->assertContains('aktif', $fillable);
        $this->assertContains('is_default', $fillable);
        $this->assertContains('sumber', $fillable);
    }

    public function test_tambah_stok(): void
    {
        $sampah = $this->createSampah(['stok' => 10]);
        
        $model = new \Modules\BankSampah\Models\BankSampahSampah();
        $model->id = $sampah->id;
        $model->stok = 10;
        
        $result = $model->tambahStok(5);
        
        $this->assertTrue($result);
        
        $stok = \DB::table('bank_sampah_sampah')
            ->where('id', $sampah->id)
            ->value('stok');
        
        $this->assertEquals(15, $stok);
    }

    public function test_kurangi_stok_sufficient(): void
    {
        $sampah = $this->createSampah(['stok' => 10]);
        
        $model = new \Modules\BankSampah\Models\BankSampahSampah();
        $model->id = $sampah->id;
        $model->stok = 10;
        
        $result = $model->kurangiStok(5);
        
        $this->assertTrue($result);
        
        $stok = \DB::table('bank_sampah_sampah')
            ->where('id', $sampah->id)
            ->value('stok');
        
        $this->assertEquals(5, $stok);
    }

    public function test_kurangi_stok_insufficient(): void
    {
        $sampah = $this->createSampah(['stok' => 3]);
        
        $model = new \Modules\BankSampah\Models\BankSampahSampah();
        $model->id = $sampah->id;
        $model->stok = 3;
        
        $result = $model->kurangiStok(5);
        
        $this->assertFalse($result);
        
        // Stok should remain unchanged
        $stok = \DB::table('bank_sampah_sampah')
            ->where('id', $sampah->id)
            ->value('stok');
        
        $this->assertEquals(3, $stok);
    }

    public function test_kategori_relationship(): void
    {
        $sampah = new \Modules\BankSampah\Models\BankSampahSampah();
        $relation = $sampah->kategori();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    }

    public function test_transaksi_detail_relationship(): void
    {
        $sampah = new \Modules\BankSampah\Models\BankSampahSampah();
        $relation = $sampah->transaksiDetail();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    }
}
