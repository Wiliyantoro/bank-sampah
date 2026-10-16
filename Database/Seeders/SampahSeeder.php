<?php

/*
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 */

namespace Modules\BankSampah\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Idempotent: skip jika DESA INI sudah punya data sampah.
     */
    public function run(): void
    {
        $configId = identitas('id');

        // Skip jika DESA INI sudah punya data sampah
        $exists = DB::table('bank_sampah_sampah')
            ->where('config_id', $configId)
            ->exists();
        if ($exists) {
            return;
        }

        // Resolve kategori_id berdasarkan nama kategori DESA INI
        // (karena ID bisa berbeda antar desa jika ada custom data)
        $kategoriMap = $this->resolveKategoriMap($configId);

        $now = now();
        $sampah = [
            // === Organik ===
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['Organik'],   'nama' => 'Sisa Makanan',      'satuan' => 'kg',  'harga_beli' => 200,    'harga_jual' => 500,    'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['Organik'],   'nama' => 'Daun Kering',        'satuan' => 'kg',  'harga_beli' => 100,    'harga_jual' => 300,    'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['Organik'],   'nama' => 'Ranting/Pohon',      'satuan' => 'kg',  'harga_beli' => 50,     'harga_jual' => 200,    'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],

            // === Anorganik ===
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['Anorganik'], 'nama' => 'Botol Plastik (PET)', 'satuan' => 'kg', 'harga_beli' => 1500,   'harga_jual' => 3500,   'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['Anorganik'], 'nama' => 'Plastik Campur',     'satuan' => 'kg',  'harga_beli' => 500,    'harga_jual' => 1500,   'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['Anorganik'], 'nama' => 'Plastik Kresek',     'satuan' => 'kg',  'harga_beli' => 200,    'harga_jual' => 800,    'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],

            // === Kertas ===
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['Kertas'],    'nama' => 'Kertas HVS',         'satuan' => 'kg',  'harga_beli' => 1000,   'harga_jual' => 2500,   'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['Kertas'],    'nama' => 'Koran/Majalah',      'satuan' => 'kg',  'harga_beli' => 800,    'harga_jual' => 2000,   'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['Kertas'],    'nama' => 'Kardus',             'satuan' => 'kg',  'harga_beli' => 1200,   'harga_jual' => 3000,   'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],

            // === Logam ===
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['Logam'],     'nama' => 'Kaleng Aluminium',   'satuan' => 'kg',  'harga_beli' => 5000,   'harga_jual' => 10000,  'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['Logam'],     'nama' => 'Besi',               'satuan' => 'kg',  'harga_beli' => 2000,   'harga_jual' => 5000,   'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['Logam'],     'nama' => 'Tembaga',            'satuan' => 'kg',  'harga_beli' => 15000,  'harga_jual' => 30000,  'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],

            // === Kaca ===
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['Kaca'],      'nama' => 'Botol Kaca',         'satuan' => 'kg',  'harga_beli' => 300,    'harga_jual' => 1000,   'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['Kaca'],      'nama' => 'Pecahan Kaca',       'satuan' => 'kg',  'harga_beli' => 100,    'harga_jual' => 500,    'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],

            // === B3 ===
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['B3'],        'nama' => 'Baterai',            'satuan' => 'kg',  'harga_beli' => 3000,   'harga_jual' => 8000,   'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
            ['config_id' => $configId, 'kategori_id' => $kategoriMap['B3'],        'nama' => 'Lampu Neon',         'satuan' => 'pcs', 'harga_beli' => 2000,   'harga_jual' => 5000,   'stok' => 0, 'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('bank_sampah_sampah')->insert($sampah);
    }

    /**
     * Resolve kategori_id berdasarkan nama kategori untuk desa tertentu.
     * Mengembalikan array ['Organik' => 1, 'Anorganik' => 2, ...]
     */
    private function resolveKategoriMap(int $configId): array
    {
        $kategoriNames = ['Organik', 'Anorganik', 'B3', 'Kertas', 'Logam', 'Kaca'];
        $map = [];

        foreach ($kategoriNames as $nama) {
            $row = DB::table('bank_sampah_kategori')
                ->where('config_id', $configId)
                ->where('nama', $nama)
                ->first();

            if ($row) {
                $map[$nama] = $row->id;
            }
        }

        return $map;
    }
}
