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

use App\Traits\Migrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\BankSampah\Database\Seeders\BankSampahSeeder;

return new class () extends Migration {
    use Migrator;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Jalankan seeder untuk register module ke navigasi OpenSID
        (new BankSampahSeeder())->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $id = identitas('id');
        $this->deleteModul(['config_id' => $id, 'slug' => 'bank-sampah']);
        $this->deleteModul(['config_id' => $id, 'slug' => 'bank-sampah-dashboard']);
        $this->deleteModul(['config_id' => $id, 'slug' => 'bank-sampah-member']);
        $this->deleteModul(['config_id' => $id, 'slug' => 'bank-sampah-transaksi']);
        $this->deleteModul(['config_id' => $id, 'slug' => 'bank-sampah-sampah']);
        $this->deleteModul(['config_id' => $id, 'slug' => 'bank-sampah-kategori']);
        $this->deleteModul(['config_id' => $id, 'slug' => 'bank-sampah-lokasi']);
        $this->deleteModul(['config_id' => $id, 'slug' => 'bank-sampah-reward']);
        $this->deleteModul(['config_id' => $id, 'slug' => 'bank-sampah-laporan']);

        // Hapus pengaturan milik modul (key berawalan bank_sampah_)
        DB::table('setting_aplikasi')
            ->where('config_id', $id)
            ->where('key', 'like', 'bank_sampah_%')
            ->delete();
    }
};
