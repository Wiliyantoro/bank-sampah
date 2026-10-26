<?php

namespace Modules\BankSampah\Tests\Feature;

use Modules\BankSampah\Models\KategoriModel;
use Modules\BankSampah\Tests\TestCase;

/**
 * Feature test CRUD Kategori — dijalankan di dalam inti (pola
 * modul resmi): salin tests/Feature/Modules/BankSampah ke
 * tests/Feature/Modules/BankSampah pada repo inti, lalu jalankan
 * vendor/bin/phpunit tests/Feature/Modules/BankSampah.
 */
class KategoriHttpTest extends TestCase
{
    public function testIndexMemuatDaftarKategori(): void
    {
        $this->createKategori(['nama' => 'Plastik']);

        $response = $this->get(ci_route('bank-sampah.kategori-sampah'));

        $response->assertStatus(200);
        $response->assertSee('Plastik', false);
    }

    public function testInsertKategoriBaru(): void
    {
        $response = $this->post(ci_route('bank-sampah.kategori-sampah/insert'), [
            'nama'       => 'Kertas',
            'keterangan' => 'Kertas bekas',
            'aktif'      => 1,
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('bank_sampah_kategori', [
            'config_id' => $this->configId,
            'nama'      => 'Kertas',
        ]);
    }

    public function testInsertWajibNama(): void
    {
        $response = $this->post(ci_route('bank-sampah.kategori-sampah/insert'), [
            'nama' => '',
        ]);

        $response->assertSessionHasErrors(['nama']);
    }

    public function testDeleteKategori(): void
    {
        $kategori = KategoriModel::create([
            'config_id'  => $this->configId,
            'nama'       => 'Uji Hapus',
            'aktif'      => 1,
            'is_default' => 0,
            'sumber'     => 'system',
        ]);

        $this->post(ci_route('bank-sampah.kategori-sampah/delete/' . $kategori->id));

        $this->assertDatabaseMissing('bank_sampah_kategori', [
            'id' => $kategori->id,
        ]);
    }

    public function testKategoriTerisolasiPerDesa(): void
    {
        $this->createKategori(['nama' => 'Desa Satu']);

        $this->assertEquals(0, KategoriModel::where('config_id', 2)->count());
    }
}
