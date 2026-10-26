<?php

namespace Modules\BankSampah\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Modules\BankSampah\Tests\TestCase;

/**
 * Feature test transaksi setor — dijalankan di dalam inti.
 * Mencakup regresi issue #67: lokasi_id kosong harus NULL, bukan ''.
 */
class TransaksiSetorHttpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seedMemberDenganSaldo();
    }

    protected function seedMemberDenganSaldo(): void
    {
        DB::table('bank_sampah_member')->insert([
            'config_id'    => $this->configId,
            'penduduk_id'  => 1,
            'no_member'    => 'BS-TEST-01',
            'nama'         => 'Nasabah Uji',
            'aktif'        => 1,
            'saldo'        => 0,
            'poin'         => 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        DB::table('bank_sampah_sampah')->insert([
            'config_id'   => $this->configId,
            'kategori_id' => 1,
            'nama'        => 'Sisa Makanan',
            'satuan'      => 'kg',
            'harga'       => 200,
            'poin_per_kg' => 10,
            'aktif'       => 1,
            'stok'        => 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    protected function memberId(): int
    {
        return (int) DB::table('bank_sampah_member')->where('no_member', 'BS-TEST-01')->value('id');
    }

    public function testSetorTanpaLokasiTersimpanLokasiNull(): void
    {
        $response = $this->post(ci_route('bank-sampah.transaksi/proses-setor'), [
            'member_id'            => $this->memberId(),
            'lokasi_id'            => '',
            'item'                 => [
                ['sampah_id' => 1, 'berat' => 2],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $transaksi = DB::table('bank_sampah_transaksi')
            ->where('member_id', $this->memberId())
            ->first();

        $this->assertNotNull($transaksi);
        $this->assertNull($transaksi->lokasi_id);
        $this->assertEquals(400.00, (float) $transaksi->total);

        $detail = DB::table('bank_sampah_transaksi_detail')
            ->where('transaksi_id', $transaksi->id)
            ->get();
        $this->assertCount(1, $detail);
    }

    public function testSetorMenambahSaldoDanPoin(): void
    {
        $this->post(ci_route('bank-sampah.transaksi/proses-setor'), [
            'member_id' => $this->memberId(),
            'item'      => [
                ['sampah_id' => 1, 'berat' => 5],
            ],
        ]);

        $member = DB::table('bank_sampah_member')->where('id', $this->memberId())->first();

        $this->assertEquals(1000.00, (float) $member->saldo);
        $this->assertEquals(50, (int) $member->poin);
    }

    public function testSetorBeratNolDitolak(): void
    {
        $this->post(ci_route('bank-sampah.transaksi/proses-setor'), [
            'member_id' => $this->memberId(),
            'item'      => [
                ['sampah_id' => 1, 'berat' => 0],
            ],
        ]);

        $this->assertEquals(0, DB::table('bank_sampah_transaksi')->count());
    }
}
