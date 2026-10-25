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
        Model::unguard();

        $id = identitas('id');

        // Menu utama Bank Sampah
        $this->createModul([
            'config_id' => $id,
            'modul'  => 'Bank Sampah',
            'url'    => 'bank-sampah',
            'slug'   => 'bank-sampah',
            'ikon'   => 'fa-recycle',
            'level'  => 1,
            'parent' => 0,
        ]);

        // Sub menu (slug = $sub_modul_ini tiap controller backend)
        $this->createModuls([
            [
                'modul'       => 'Dashboard',
                'url'         => 'bank-sampah/dashboard',
                'slug'        => 'bank-sampah-dashboard',
                'ikon'        => 'fa-dashboard',
                'level'       => 2,
                'parent_slug' => 'bank-sampah',
            ],
            [
                'modul'       => 'Nasabah',
                'url'         => 'bank-sampah/member',
                'slug'        => 'bank-sampah-member',
                'ikon'        => 'fa-users',
                'level'       => 2,
                'parent_slug' => 'bank-sampah',
            ],
            [
                'modul'       => 'Setor / Tarik',
                'url'         => 'bank-sampah/transaksi',
                'slug'        => 'bank-sampah-transaksi',
                'ikon'        => 'fa-exchange',
                'level'       => 2,
                'parent_slug' => 'bank-sampah',
            ],
            [
                'modul'       => 'Jenis Sampah',
                'url'         => 'bank-sampah/jenis-sampah',
                'slug'        => 'bank-sampah-sampah',
                'ikon'        => 'fa-cubes',
                'level'       => 2,
                'parent_slug' => 'bank-sampah',
            ],
            [
                'modul'       => 'Kategori',
                'url'         => 'bank-sampah/kategori-sampah',
                'slug'        => 'bank-sampah-kategori',
                'ikon'        => 'fa-tags',
                'level'       => 2,
                'parent_slug' => 'bank-sampah',
            ],
            [
                'modul'       => 'Lokasi (TPS)',
                'url'         => 'bank-sampah/lokasi',
                'slug'        => 'bank-sampah-lokasi',
                'ikon'        => 'fa-map-marker',
                'level'       => 2,
                'parent_slug' => 'bank-sampah',
            ],
            [
                'modul'       => 'Reward & Poin',
                'url'         => 'bank-sampah/reward',
                'slug'        => 'bank-sampah-reward',
                'ikon'        => 'fa-gift',
                'level'       => 2,
                'parent_slug' => 'bank-sampah',
            ],
            [
                'modul'       => 'Laporan',
                'url'         => 'bank-sampah/laporan',
                'slug'        => 'bank-sampah-laporan',
                'ikon'        => 'fa-file-pdf-o',
                'level'       => 2,
                'parent_slug' => 'bank-sampah',
            ],
        ]);
    }
}
