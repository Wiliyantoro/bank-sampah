<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 *
 */

namespace Modules\BankSampah\Database\Seeders;

use App\Traits\Migrator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ModulSeeder extends Seeder
{
    use Migrator;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // CRI safety: hanya table core OpenSID yang diperlukan
        if (! Schema::hasTable('setting_modul')) {
            $this->command?->warn('[BankSampah] ModulSeeder skip: table setting_modul belum ada.');
            return;
        }

        // Skip jika modul sudah terdaftar (idempotent)
        $exists = \DB::table('setting_modul')
            ->where('slug', 'bank-sampah')
            ->where('config_id', identitas('id'))
            ->exists();
        if ($exists) {
            return;
        }

        Model::unguard();

        // Menu utama Bank Sampah
        $this->createModul([
            'modul'  => 'Bank Sampah',
            'url'    => 'bank-sampah',
            'slug'   => 'bank-sampah',
            'ikon'   => 'fa-recycle',
            'level'  => 1,
            'parent' => 0,
        ]);

        // Sub menu: Dashboard
        $this->createModul([
            'modul'       => 'Dashboard',
            'url'         => 'bank-sampah/dashboard',
            'slug'        => 'bank-sampah-dashboard',
            'ikon'        => 'fa-dashboard',
            'level'       => 2,
            'parent_slug' => 'bank-sampah',
        ]);

        // Sub menu: Nasabah
        $this->createModul([
            'modul'       => 'Nasabah',
            'url'         => 'bank-sampah/member',
            'slug'        => 'bank-sampah-member',
            'ikon'        => 'fa-users',
            'level'       => 2,
            'parent_slug' => 'bank-sampah',
        ]);

        // Sub menu: Setor / Tarik
        $this->createModul([
            'modul'       => 'Setor / Tarik',
            'url'         => 'bank-sampah/transaksi',
            'slug'        => 'bank-sampah-transaksi',
            'ikon'        => 'fa-exchange',
            'level'       => 2,
            'parent_slug' => 'bank-sampah',
        ]);

        // Sub menu: Jenis Sampah
        $this->createModul([
            'modul'       => 'Jenis Sampah',
            'url'         => 'bank-sampah/jenis-sampah',
            'slug'        => 'bank-sampah-sampah',
            'ikon'        => 'fa-cubes',
            'level'       => 2,
            'parent_slug' => 'bank-sampah',
        ]);

        // Sub menu: Kategori
        $this->createModul([
            'modul'       => 'Kategori',
            'url'         => 'bank-sampah/kategori-sampah',
            'slug'        => 'bank-sampah-kategori',
            'ikon'        => 'fa-tags',
            'level'       => 2,
            'parent_slug' => 'bank-sampah',
        ]);

        // Sub menu: Lokasi / TPS
        $this->createModul([
            'modul'       => 'Lokasi (TPS)',
            'url'         => 'bank-sampah/lokasi',
            'slug'        => 'bank-sampah-lokasi',
            'ikon'        => 'fa-map-marker',
            'level'       => 2,
            'parent_slug' => 'bank-sampah',
        ]);

        // Sub menu: Reward & Poin
        $this->createModul([
            'modul'       => 'Reward & Poin',
            'url'         => 'bank-sampah/reward',
            'slug'        => 'bank-sampah-reward',
            'ikon'        => 'fa-gift',
            'level'       => 2,
            'parent_slug' => 'bank-sampah',
        ]);

        // Sub menu: Laporan
        $this->createModul([
            'modul'       => 'Laporan',
            'url'         => 'bank-sampah/laporan',
            'slug'        => 'bank-sampah-laporan',
            'ikon'        => 'fa-file-pdf-o',
            'level'       => 2,
            'parent_slug' => 'bank-sampah',
        ]);
    }
}
