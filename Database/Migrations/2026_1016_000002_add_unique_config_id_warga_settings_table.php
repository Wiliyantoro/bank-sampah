<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
 * BS-027: UNIQUE(config_id) pada bank_sampah_warga_settings
 *
 * getSettings() memakai firstOrCreate(['config_id' => $configId]); tanpa
 * UNIQUE, dua request bersamaan saat baris belum ada bisa menghasilkan
 * dua baris settings untuk desa yang sama (getSettings() lalu membaca
 * baris arbitrer — pengaturan terlihat "tidak tersimpan").
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bank_sampah_warga_settings')) {
            return;
        }

        // Hapus duplikat dulu (simpan baris tertua per config_id) — subquery
        // dibungkus derived table agar legal di MySQL (tidak boleh DELETE dan
        // SELECT tabel yang sama langsung dalam satu statement).
        DB::statement(
            'DELETE FROM bank_sampah_warga_settings WHERE id NOT IN '
            . '(SELECT id_tua FROM (SELECT MIN(id) AS id_tua FROM bank_sampah_warga_settings GROUP BY config_id) d)'
        );

        Schema::table('bank_sampah_warga_settings', static function (Blueprint $table) {
            $table->unique('config_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('bank_sampah_warga_settings')) {
            return;
        }

        Schema::table('bank_sampah_warga_settings', static function (Blueprint $table) {
            $table->dropUnique(['config_id']);
        });
    }
};
