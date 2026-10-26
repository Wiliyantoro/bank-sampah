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

class TransaksiModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createBankSampahTables();
    }

    public function test_table_name(): void
    {
        $model = new \Modules\BankSampah\Models\TransaksiModel();
        $this->assertEquals('bank_sampah_transaksi', $model->getTable());
    }

    public function test_fillable_attributes(): void
    {
        $model = new \Modules\BankSampah\Models\TransaksiModel();
        $fillable = $model->getFillable();
        
        $this->assertContains('config_id', $fillable);
        $this->assertContains('no_transaksi', $fillable);
        $this->assertContains('member_id', $fillable);
        $this->assertContains('tipe', $fillable);
        $this->assertContains('total', $fillable);
    }

    public function test_generate_no_transaksi_format(): void
    {
        $noTransaksi = \Modules\BankSampah\Models\TransaksiModel::generateNoTransaksi();
        
        // Format: TRX-YYYYMMDD-XXXX (4+ digit — 5+ digit bila overflow > 9999, BS-022)
        $this->assertMatchesRegularExpression('/^TRX-\d{8}-\d{4,}$/', $noTransaksi);
    }

    public function test_generate_no_transaksi_increments(): void
    {
        // Create existing transaksi
        $this->createTransaksi([
            'no_transaksi' => 'TRX-' . date('Ymd') . '-0001'
        ]);
        
        // Generate new number
        $noTransaksi = \Modules\BankSampah\Models\TransaksiModel::generateNoTransaksi();
        
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
        $noTransaksi = \Modules\BankSampah\Models\TransaksiModel::generateNoTransaksi();
        
        // Should start from 0001 for new day
        $this->assertStringEndsWith('-0001', $noTransaksi);
    }

    public function test_generate_no_transaksi_overflow_past_9999(): void
    {
        // BS-022: urut ke-10000 harus 5 digit, bukan kembali ke 0000 (duplikat)
        $this->createTransaksi([
            'no_transaksi' => 'TRX-' . date('Ymd') . '-9999',
        ]);

        $noTransaksi = \Modules\BankSampah\Models\TransaksiModel::generateNoTransaksi();

        $this->assertStringEndsWith('-10000', $noTransaksi);
    }

    public function test_generate_no_transaksi_increments_after_overflow(): void
    {
        // BS-022: parse 5 digit — lanjutan 10001, bukan terpotong jadi 0001
        $this->createTransaksi([
            'no_transaksi' => 'TRX-' . date('Ymd') . '-10000',
        ]);

        $noTransaksi = \Modules\BankSampah\Models\TransaksiModel::generateNoTransaksi();

        $this->assertStringEndsWith('-10001', $noTransaksi);
    }

    public function test_member_relationship(): void
    {
        $transaksi = new \Modules\BankSampah\Models\TransaksiModel();
        $relation = $transaksi->member();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    }

    public function test_lokasi_relationship(): void
    {
        $transaksi = new \Modules\BankSampah\Models\TransaksiModel();
        $relation = $transaksi->lokasi();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    }

    public function test_detail_relationship(): void
    {
        $transaksi = new \Modules\BankSampah\Models\TransaksiModel();
        $relation = $transaksi->detail();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    }
}
