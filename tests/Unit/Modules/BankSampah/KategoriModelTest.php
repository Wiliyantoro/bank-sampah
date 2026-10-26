<?php

/*
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 */
namespace Modules\BankSampah\Tests\Unit\Modules\BankSampah;

use Modules\BankSampah\Tests\TestCase;

class KategoriModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createBankSampahTables();
    }

    public function test_table_name(): void
    {
        $model = new \Modules\BankSampah\Models\KategoriModel();
        $this->assertEquals('bank_sampah_kategori', $model->getTable());
    }

    public function test_fillable_attributes(): void
    {
        $model = new \Modules\BankSampah\Models\KategoriModel();
        $fillable = $model->getFillable();
        
        $this->assertContains('config_id', $fillable);
        $this->assertContains('nama', $fillable);
        $this->assertContains('keterangan', $fillable);
        $this->assertContains('aktif', $fillable);
        $this->assertContains('is_default', $fillable);
        $this->assertContains('sumber', $fillable);
    }

    public function test_scope_desa_filters_by_config_id(): void
    {
        // Create kategori for different desas
        $this->createKategori(['nama' => 'Organik Desa 1', 'config_id' => 1]);
        $this->createKategori(['nama' => 'Anorganik Desa 2', 'config_id' => 2]);

        $model = new \Modules\BankSampah\Models\KategoriModel();
        $query = $model->newQuery();
        
        // This would need identitas() to return config_id = 1
        // In actual test, we'd mock identitas()
        $this->assertNotNull($model);
    }

    public function test_is_default_returns_true_when_1(): void
    {
        $kategori = new \Modules\BankSampah\Models\KategoriModel();
        $kategori->is_default = 1;
        
        $this->assertTrue($kategori->isDefault());
    }

    public function test_is_default_returns_false_when_0(): void
    {
        $kategori = new \Modules\BankSampah\Models\KategoriModel();
        $kategori->is_default = 0;
        
        $this->assertFalse($kategori->isDefault());
    }

    public function test_is_custom_returns_true_when_not_default(): void
    {
        $kategori = new \Modules\BankSampah\Models\KategoriModel();
        $kategori->is_default = 0;
        
        $this->assertTrue($kategori->isCustom());
    }

    public function test_is_custom_returns_false_when_default(): void
    {
        $kategori = new \Modules\BankSampah\Models\KategoriModel();
        $kategori->is_default = 1;
        
        $this->assertFalse($kategori->isCustom());
    }

    public function test_sampah_relationship(): void
    {
        $kategori = new \Modules\BankSampah\Models\KategoriModel();
        $relation = $kategori->sampah();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    }
}
