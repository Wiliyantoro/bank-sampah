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

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Idempotent: skip jika DESA INI sudah punya data kategori.
     */
    public function run(): void
    {
        $configId = identitas('id');

        // Skip jika DESA INI sudah punya data kategori
        $exists = DB::table('bank_sampah_kategori')
            ->where('config_id', $configId)
            ->exists();
        if ($exists) {
            return;
        }

        $now = now();
        $kategori = [
            ['config_id' => $configId, 'nama' => 'Organik',   'keterangan' => 'Sampah organik (sisa makanan, daun, dll)',                      'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
            ['config_id' => $configId, 'nama' => 'Anorganik', 'keterangan' => 'Sampah anorganik/plastik (botol, kardus, kertas, dll)',          'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
            ['config_id' => $configId, 'nama' => 'B3',        'keterangan' => 'Sampah Bahan Berbahaya dan Beracun (baterai, lampu, dll)',       'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
            ['config_id' => $configId, 'nama' => 'Kertas',    'keterangan' => 'Sampah kertas (HVS, koran, majalah, kardus)',                   'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
            ['config_id' => $configId, 'nama' => 'Logam',     'keterangan' => 'Sampah logam (kaleng, besi, tembaga, aluminium)',               'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
            ['config_id' => $configId, 'nama' => 'Kaca',      'keterangan' => 'Sampah kaca (botol kaca, pecahan kaca)',                        'aktif' => 1, 'is_default' => 1, 'sumber' => 'system', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('bank_sampah_kategori')->insert($kategori);
    }
}
