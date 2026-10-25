<?php

namespace Modules\BankSampah\Tests\Unit\Modules\BankSampah;

use Modules\BankSampah\Tests\TestCase;

class SampahModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createBankSampahTables();
    }

    public function test_table_name(): void
    {
        $model = new \Modules\BankSampah\Models\SampahModel();
        $this->assertEquals('bank_sampah_sampah', $model->getTable());
    }

    public function test_fillable_attributes(): void
    {
        $model = new \Modules\BankSampah\Models\SampahModel();
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
        
        $model = new \Modules\BankSampah\Models\SampahModel();
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
        
        $model = new \Modules\BankSampah\Models\SampahModel();
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
        
        $model = new \Modules\BankSampah\Models\SampahModel();
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

    public function test_tambah_stok_atomic_uses_db_value_not_memory(): void
    {
        // BS-010: increment harus terhadap nilai DB saat ini (atomik),
        // bukan terhadap nilai in-memory yang bisa basi (stale)
        $sampah = $this->createSampah(['stok' => 7]);

        $model = new \Modules\BankSampah\Models\SampahModel();
        $model->id = $sampah->id;
        $model->stok = 10; // nilai in-memory basi, tidak boleh dijadikan dasar

        $result = $model->tambahStok(5);

        $this->assertTrue($result);
        $stok = \DB::table('bank_sampah_sampah')->where('id', $sampah->id)->value('stok');
        $this->assertEquals(12, $stok); // 7 + 5 (bukan 10 + 5)
        $this->assertEquals(12, $model->stok); // model ikut sinkron
    }

    public function test_kurangi_stok_atomic_guard_race(): void
    {
        // BS-010: guard ketersediaan dilakukan DB, bukan in-memory.
        // Nilai in-memory basi tidak boleh loloskan stok minus.
        $sampah = $this->createSampah(['stok' => 3]);

        $model = new \Modules\BankSampah\Models\SampahModel();
        $model->id = $sampah->id;
        $model->stok = 999; // basi

        $this->assertFalse($model->kurangiStok(5));

        $stok = \DB::table('bank_sampah_sampah')->where('id', $sampah->id)->value('stok');
        $this->assertEquals(3, $stok);
    }

    public function test_kategori_relationship(): void
    {
        $sampah = new \Modules\BankSampah\Models\SampahModel();
        $relation = $sampah->kategori();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    }

    public function test_transaksi_detail_relationship(): void
    {
        $sampah = new \Modules\BankSampah\Models\SampahModel();
        $relation = $sampah->transaksiDetail();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    }
}
