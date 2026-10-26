<?php

namespace Modules\BankSampah\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Modules\BankSampah\Tests\TestCase;

/**
 * Feature test member — dijalankan di dalam inti.
 */
class MemberHttpTest extends TestCase
{
    public function testIndexMemuatDaftarMember(): void
    {
        DB::table('bank_sampah_member')->insert([
            'config_id'   => $this->configId,
            'penduduk_id' => 1,
            'no_member'   => 'BS-TEST-02',
            'nama'        => 'Nasabah Dua',
            'aktif'       => 1,
            'saldo'       => 0,
            'poin'        => 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $response = $this->get(ci_route('bank-sampah.member'));

        $response->assertStatus(200);
        $response->assertSee('BS-TEST-02', false);
    }

    public function testNoMemberWajib(): void
    {
        $response = $this->post(ci_route('bank-sampah.member/insert'), [
            'nama' => 'Tanpa No Member',
        ]);

        $response->assertSessionHasErrors(['no_member']);
    }

    public function testNoMemberUnikPerDesa(): void
    {
        DB::table('bank_sampah_member')->insert([
            'config_id'   => $this->configId,
            'penduduk_id' => 1,
            'no_member'   => 'BS-DUP',
            'nama'        => 'Pertama',
            'aktif'       => 1,
            'saldo'       => 0,
            'poin'        => 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $response = $this->post(ci_route('bank-sampah.member/insert'), [
            'no_member' => 'BS-DUP',
            'nama'      => 'Duplikat',
        ]);

        $response->assertSessionHasErrors(['no_member']);
    }
}
