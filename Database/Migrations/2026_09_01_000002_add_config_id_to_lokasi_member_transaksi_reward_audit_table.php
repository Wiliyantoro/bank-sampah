<?php

/*
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        $desaId = identitas('id');
        $tables = ['bank_sampah_lokasi', 'bank_sampah_member', 'bank_sampah_transaksi', 'bank_sampah_reward', 'bank_sampah_audit_log'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tbl) use ($table) {
                    if (! Schema::hasColumn($table, 'config_id')) {
                        $tbl->integer('config_id')->nullable()->after('id');
                    }
                });

                DB::table($table)
                    ->whereNull('config_id')
                    ->update(['config_id' => $desaId]);

                Schema::table($table, function (Blueprint $tbl) {
                    $tbl->foreign('config_id')
                        ->references('id')->on('config')
                        ->onUpdate('cascade')->onDelete('cascade');
                });
            }
        }

        // Unique constraints per desa
        // lokasi: UNIQUE(config_id, kode)
        Schema::table('bank_sampah_lokasi', function (Blueprint $tbl) {
            $tbl->unique(['config_id', 'kode']);
        });

        // member: UNIQUE(config_id, no_member)
        Schema::table('bank_sampah_member', function (Blueprint $tbl) {
            $tbl->unique(['config_id', 'no_member']);
        });

        // transaksi: UNIQUE(config_id, no_transaksi)
        Schema::table('bank_sampah_transaksi', function (Blueprint $tbl) {
            $tbl->unique(['config_id', 'no_transaksi']);
        });

        // Indexes
        DB::statement('CREATE INDEX idx_lokasi_config_active ON bank_sampah_lokasi(config_id, aktif)');
        DB::statement('CREATE INDEX idx_member_config_active ON bank_sampah_member(config_id, aktif)');
        DB::statement('CREATE INDEX idx_transaksi_config_date ON bank_sampah_transaksi(config_id, created_at)');
        DB::statement('CREATE INDEX idx_transaksi_config_tipe ON bank_sampah_transaksi(config_id, tipe, created_at)');
        DB::statement('CREATE INDEX idx_reward_config_member ON bank_sampah_reward(config_id, member_id)');
        DB::statement('CREATE INDEX idx_audit_config_event ON bank_sampah_audit_log(config_id, event)');
    }

    public function down(): void
    {
        $tables = ['bank_sampah_lokasi', 'bank_sampah_member', 'bank_sampah_transaksi', 'bank_sampah_reward', 'bank_sampah_audit_log'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tbl) {
                    $tbl->dropForeign(['config_id']);
                    $tbl->dropColumn('config_id');
                });
            }
        }
    }
};
