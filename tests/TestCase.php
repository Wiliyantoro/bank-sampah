<?php

namespace Modules\BankSampah\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

/**
 * Base test case untuk modul BankSampah.
 * 
 * Menyediakan:
 * - Database in-memory (SQLite) untuk testing
 * - Mock identitas() function
 * - Setup user admin untuk testing
 */
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected int $configId = 1;
    protected int $adminUserId = 1;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock config table
        $this->setUpDatabase();

        // Mock identitas() function
        $this->mockIdentitas();

        // Setup session sebagai admin
        $this->setupAdminSession();
    }

    /**
     * Setup database test dengan data minimal
     */
    protected function setUpDatabase(): void
    {
        // Create config table
        \DB::statement('CREATE TABLE IF NOT EXISTS config (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            app_key TEXT NOT NULL DEFAULT "",
            nama_desa TEXT NOT NULL DEFAULT "Desa Test",
            kode_desa TEXT,
            nama_kecamatan TEXT NOT NULL DEFAULT "Kecamatan Test",
            nama_kabupaten TEXT NOT NULL DEFAULT "Kabupaten Test",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');

        // Insert test desa
        \DB::table('config')->insert([
            'id'              => $this->configId,
            'nama_desa'       => 'Desa Test',
            'nama_kecamatan'  => 'Kecamatan Test',
            'nama_kabupaten'  => 'Kabupaten Test',
            'created_at'      => now(),
        ]);

        // Create user table
        \DB::statement('CREATE TABLE IF NOT EXISTS user (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL DEFAULT "Admin",
            email TEXT,
            password TEXT,
            config_id INTEGER DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');

        // Insert test admin
        \DB::table('user')->insert([
            'id'         => $this->adminUserId,
            'nama'       => 'Admin Test',
            'config_id'  => $this->configId,
            'created_at' => now(),
        ]);
    }

    /**
     * Mock identitas() function untuk testing
     */
    protected function mockIdentitas(): void
    {
        if (! function_exists('identitas')) {
            // identitas() should already be defined by OpenSID
            // For testing, we create a mock
            eval('
                function identitas($key = null) {
                    $data = ["id" => ' . $this->configId . '];
                    if ($key) {
                        return $data[$key] ?? null;
                    }
                    return (object) $data;
                }
            ');
        }
    }

    /**
     * Setup session sebagai admin yang sudah login
     */
    protected function setupAdminSession(): void
    {
        // Simulate CI3 admin session
        Session::put('isAdmin', true);
        Session::put('user_id', $this->adminUserId);
    }

    /**
     * Helper: buat data kategori test
     */
    protected function createKategori(array $overrides = []): object
    {
        $data = array_merge([
            'config_id'  => $this->configId,
            'nama'       => 'Organik',
            'keterangan' => 'Sampah organik',
            'aktif'      => 1,
            'is_default' => 1,
            'sumber'     => 'system',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides);

        \DB::table('bank_sampah_kategori')->insert($data);
        
        return (object) $data;
    }

    /**
     * Helper: buat data lokasi test
     */
    protected function createLokasi(array $overrides = []): object
    {
        $data = array_merge([
            'config_id'           => $this->configId,
            'nama'                => 'TPS Utama',
            'kode'                => 'TPS-001',
            'alamat'              => 'Jl. Test No. 1',
            'penanggung_jawab'    => 'Pak Test',
            'no_hp'               => '081234567890',
            'aktif'               => 1,
            'is_default'          => 0,
            'sumber'              => 'desa',
            'created_at'          => now(),
            'updated_at'          => now(),
        ], $overrides);

        \DB::table('bank_sampah_lokasi')->insert($data);
        
        return (object) $data;
    }

    /**
     * Helper: buat data sampah test
     */
    protected function createSampah(array $overrides = []): object
    {
        // Pastikan kategori ada
        $kategoriId = $overrides['kategori_id'] ?? 1;
        if (! \DB::table('bank_sampah_kategori')->where('id', $kategoriId)->exists()) {
            $this->createKategori(['id' => $kategoriId]);
        }

        $data = array_merge([
            'config_id'   => $this->configId,
            'kategori_id' => $kategoriId,
            'nama'        => 'Sisa Makanan',
            'satuan'      => 'kg',
            'harga_beli'  => 200,
            'harga_jual'  => 500,
            'stok'        => 0,
            'aktif'       => 1,
            'is_default'  => 0,
            'sumber'      => 'desa',
            'created_at'  => now(),
            'updated_at'  => now(),
        ], $overrides);

        \DB::table('bank_sampah_sampah')->insert($data);
        
        return (object) $data;
    }

    /**
     * Helper: buat data member test
     */
    protected function createMember(array $overrides = []): object
    {
        $data = array_merge([
            'config_id'   => $this->configId,
            'uuid'        => \Illuminate\Support\Str::uuid()->toString(),
            'no_member'   => 'BS-00001',
            'nama'        => 'Nasabah Test',
            'alamat'      => 'Jl. Test No. 1',
            'no_hp'       => '081234567890',
            'pin'         => null,
            'penduduk_id' => null,
            'lokasi_id'   => null,
            'saldo'       => 0,
            'poin'        => 0,
            'aktif'       => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ], $overrides);

        \DB::table('bank_sampah_member')->insert($data);
        
        return (object) $data;
    }

    /**
     * Helper: buat data transaksi test
     */
    protected function createTransaksi(array $overrides = []): object
    {
        // Pastikan member ada
        $memberId = $overrides['member_id'] ?? 1;
        if (! \DB::table('bank_sampah_member')->where('id', $memberId)->exists()) {
            $this->createMember(['id' => $memberId]);
        }

        $data = array_merge([
            'config_id'    => $this->configId,
            'no_transaksi' => 'TRX-' . date('Ymd') . '-0001',
            'member_id'    => $memberId,
            'lokasi_id'    => null,
            'tipe'         => 'setor',
            'total'        => 10000,
            'bayar'        => 10000,
            'potongan'     => 0,
            'poin'         => 100,
            'keterangan'   => 'Test transaksi',
            'user_id'      => $this->adminUserId,
            'created_at'   => now(),
            'updated_at'   => now(),
        ], $overrides);

        \DB::table('bank_sampah_transaksi')->insert($data);
        
        return (object) $data;
    }

    /**
     * Helper: buat data reward test
     */
    protected function createReward(array $overrides = []): object
    {
        // Pastikan member ada
        $memberId = $overrides['member_id'] ?? 1;
        if (! \DB::table('bank_sampah_member')->where('id', $memberId)->exists()) {
            $this->createMember(['id' => $memberId]);
        }

        $data = array_merge([
            'config_id'    => $this->configId,
            'member_id'    => $memberId,
            'transaksi_id' => null,
            'tipe'         => 'add',
            'poin'         => 100,
            'keterangan'   => 'Poin dari setor',
            'created_at'   => now(),
            'updated_at'   => now(),
        ], $overrides);

        \DB::table('bank_sampah_reward')->insert($data);
        
        return (object) $data;
    }

    /**
     * Helper: buat tabel BankSampah untuk testing
     */
    protected function createBankSampahTables(): void
    {
        // Kategori
        \DB::statement('CREATE TABLE IF NOT EXISTS bank_sampah_kategori (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            config_id INTEGER,
            nama VARCHAR(100) NOT NULL,
            keterangan TEXT,
            is_default TINYINT DEFAULT 0,
            sumber VARCHAR(20) DEFAULT "system",
            aktif TINYINT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');

        // Sampah
        \DB::statement('CREATE TABLE IF NOT EXISTS bank_sampah_sampah (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            config_id INTEGER,
            kategori_id INTEGER NOT NULL,
            nama VARCHAR(150) NOT NULL,
            satuan VARCHAR(20) DEFAULT "kg",
            harga_beli DECIMAL(15,2) DEFAULT 0,
            harga_jual DECIMAL(15,2) DEFAULT 0,
            stok DECIMAL(10,2) DEFAULT 0,
            aktif TINYINT DEFAULT 1,
            is_default TINYINT DEFAULT 0,
            sumber VARCHAR(20) DEFAULT "system",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');

        // Lokasi
        \DB::statement('CREATE TABLE IF NOT EXISTS bank_sampah_lokasi (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            config_id INTEGER,
            nama VARCHAR(150) NOT NULL,
            kode VARCHAR(20) NOT NULL,
            alamat TEXT,
            penanggung_jawab VARCHAR(150),
            no_hp VARCHAR(20),
            aktif TINYINT DEFAULT 1,
            is_default TINYINT DEFAULT 0,
            sumber VARCHAR(20) DEFAULT "system",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');

        // Member
        \DB::statement('CREATE TABLE IF NOT EXISTS bank_sampah_member (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            config_id INTEGER,
            uuid VARCHAR(36) NOT NULL,
            no_member VARCHAR(20) NOT NULL,
            nama VARCHAR(150) NOT NULL,
            alamat TEXT,
            no_hp VARCHAR(20),
            pin VARCHAR(255),
            penduduk_id INTEGER,
            lokasi_id INTEGER,
            saldo DECIMAL(15,2) DEFAULT 0,
            poin INTEGER DEFAULT 0,
            aktif TINYINT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');

        // Transaksi
        \DB::statement('CREATE TABLE IF NOT EXISTS bank_sampah_transaksi (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            config_id INTEGER,
            no_transaksi VARCHAR(30) NOT NULL,
            member_id INTEGER NOT NULL,
            lokasi_id INTEGER,
            tipe VARCHAR(10) NOT NULL,
            total DECIMAL(15,2) DEFAULT 0,
            bayar DECIMAL(15,2) DEFAULT 0,
            potongan DECIMAL(15,2) DEFAULT 0,
            poin INTEGER DEFAULT 0,
            keterangan TEXT,
            user_id INTEGER,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');

        // Transaksi Detail
        \DB::statement('CREATE TABLE IF NOT EXISTS bank_sampah_transaksi_detail (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            transaksi_id INTEGER NOT NULL,
            sampah_id INTEGER NOT NULL,
            berat DECIMAL(10,2) DEFAULT 0,
            harga DECIMAL(15,2) DEFAULT 0,
            subtotal DECIMAL(15,2) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');

        // Reward
        \DB::statement('CREATE TABLE IF NOT EXISTS bank_sampah_reward (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            config_id INTEGER,
            member_id INTEGER NOT NULL,
            transaksi_id INTEGER,
            tipe VARCHAR(10) NOT NULL,
            poin INTEGER NOT NULL,
            keterangan VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');

        // Warga Settings
        \DB::statement('CREATE TABLE IF NOT EXISTS bank_sampah_warga_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            config_id INTEGER NOT NULL,
            warga_aktif TINYINT DEFAULT 1,
            show_saldo TINYINT DEFAULT 1,
            show_riwayat TINYINT DEFAULT 1,
            show_poin TINYINT DEFAULT 1,
            require_pin TINYINT DEFAULT 1,
            max_riwayat INTEGER DEFAULT 10,
            pesan_selamat_datang TEXT,
            pesan_footer TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');

        // Audit Log
        \DB::statement('CREATE TABLE IF NOT EXISTS bank_sampah_audit_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            config_id INTEGER,
            event VARCHAR(100) NOT NULL,
            user_id INTEGER,
            ip_address VARCHAR(45),
            user_agent VARCHAR(500),
            data TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
    }
}
