<?php

namespace Modules\BankSampah\Tests\Feature;

use Modules\BankSampah\Http\Middleware\RequireAdminAuth;
use Modules\BankSampah\Tests\TestCase;

/**
 * Test middleware RequireAdminAuth (BS-004).
 *
 * Middleware harus:
 * - Menolak grup TANPA akses modul bank-sampah (403, fail-closed)
 * - Meloloskan grup DENGAN akses modul bank-sampah
 * - Tetap menolak (403) saat tabel permission tidak tersedia (fail-closed)
 */
class RequireAdminAuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tabel permission inti OpenSID yang diperlukan middleware
        \DB::statement('CREATE TABLE IF NOT EXISTS setting_modul (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            config_id INTEGER,
            modul TEXT,
            slug TEXT,
            aktif INTEGER DEFAULT 1
        )');

        \DB::statement('CREATE TABLE IF NOT EXISTS grup_akses (
            id_grup INTEGER,
            id_modul INTEGER,
            akses INTEGER DEFAULT 1
        )');

        \DB::table('setting_modul')->insertOrIgnore([
            'id'        => 1,
            'config_id' => $this->configId,
            'modul'     => 'Bank Sampah',
            'slug'      => 'bank-sampah',
            'aktif'     => 1,
        ]);
    }

    /**
     * Buat session admin CI3 dengan id_grup tertentu.
     */
    protected function buatAdmin(int $grupId): void
    {
        \Session::put('isAdmin', (object) [
            'id'      => $this->adminUserId,
            'nama'    => 'Admin Test',
            'id_grup' => $grupId,
        ]);
    }

    /**
     * Request yang memasuki middleware banksampah.auth.
     */
    protected function buatRequest(array $headers = [])
    {
        $request = \Illuminate\Http\Request::create('/bank-sampah', 'GET', [], [], [], $headers);
        $request->setLaravelSession($this->app['session.store']);

        return $request;
    }

    public function test_grup_tanpa_akses_ditolak_403(): void
    {
        // Grup 99 tidak punya baris grup_akses untuk modul bank-sampah
        $this->buatAdmin(99);

        $middleware = new RequireAdminAuth();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle($this->buatRequest(), fn ($req) => response('lanjut'));
    }

    public function test_grup_dengan_akses_lolos(): void
    {
        \DB::table('grup_akses')->insert(['id_grup' => 1, 'id_modul' => 1, 'akses' => 1]);
        $this->buatAdmin(1);

        $middleware = new RequireAdminAuth();
        $response   = $middleware->handle($this->buatRequest(), fn ($req) => response('lanjut'));

        $this->assertEquals('lanjut', $response->getContent());
    }

    public function test_grup_akses_slug_modul_lain_ditolak(): void
    {
        // Grup punya akses tabel setting_modul slug 'modul-lain', bukan 'bank-sampah'
        \DB::table('setting_modul')->insert([
            'id'        => 2,
            'config_id' => $this->configId,
            'modul'     => 'Modul Lain',
            'slug'      => 'modul-lain',
            'aktif'     => 1,
        ]);
        \DB::table('grup_akses')->insert(['id_grup' => 2, 'id_modul' => 2, 'akses' => 1]);
        $this->buatAdmin(2);

        $middleware = new RequireAdminAuth();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle($this->buatRequest(), fn ($req) => response('lanjut'));
    }

    public function test_fail_closed_saat_tabel_permission_hilang(): void
    {
        \DB::statement('DROP TABLE setting_modul');
        $this->buatAdmin(1);

        $middleware = new RequireAdminAuth();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle($this->buatRequest(), fn ($req) => response('lanjut'));
    }

    public function test_grup_tanpa_akses_json_dapat_403(): void
    {
        $this->buatAdmin(99);

        $middleware = new RequireAdminAuth();
        $response   = $middleware->handle(
            $this->buatRequest(['HTTP_ACCEPT' => 'application/json']),
            fn ($req) => response('lanjut')
        );

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContainsString('Forbidden', $response->getContent());
    }

    public function test_tanpa_login_diarahkan_ke_siteman(): void
    {
        // Session kosong (hapus mock admin dari TestCase::setUp)
        \Session::forget('isAdmin');

        $middleware = new RequireAdminAuth();
        $response   = $middleware->handle($this->buatRequest(), fn ($req) => response('lanjut'));

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('siteman', $response->getTargetUrl());
    }
}
