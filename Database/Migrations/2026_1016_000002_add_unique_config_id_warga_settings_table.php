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
 * #30: penambahan unique di-guard introspeksi agar migrasi modul yang
 * diulang oleh Migrasi_module CRI (modul terlanjur dipasang via CLI)
 * tidak gagal errno-1061 (duplicate key name).
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

        if (! $this->hasUnique('bank_sampah_warga_settings', ['config_id'])) {
            Schema::table('bank_sampah_warga_settings', static function (Blueprint $table) {
                $table->unique('config_id');
            });
        }
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

    /**
     * Cek unique index dengan susunan kolom tertentu sudah ada (#30).
     */
    private function hasUnique(string $table, array $columns): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['unique'] ?? false)
                && array_values($index['columns'] ?? []) === $columns) {
                return true;
            }
        }

        return false;
    }
};
