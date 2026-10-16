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
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $desaId = identitas('id');

        // ============================================================
        // 1. TABEL BANK_SAMPAH_KATEGORI
        // ============================================================
        if (Schema::hasTable('bank_sampah_kategori')) {
            Schema::table('bank_sampah_kategori', function (Blueprint $table) use ($desaId) {
                // Tambah config_id
                if (! Schema::hasColumn('bank_sampah_kategori', 'config_id')) {
                    $table->integer('config_id')->nullable()->after('id');
                }
                // Tambah is_default
                if (! Schema::hasColumn('bank_sampah_kategori', 'is_default')) {
                    $table->tinyInteger('is_default')->default(0)->after('keterangan');
                }
                // Tambah sumber
                if (! Schema::hasColumn('bank_sampah_kategori', 'sumber')) {
                    $table->string('sumber', 20)->default('system')->after('is_default');
                }
            });

            // Set config_id untuk data existing (seeder lama)
            DB::table('bank_sampah_kategori')
                ->whereNull('config_id')
                ->update([
                    'config_id'  => $desaId,
                    'is_default' => 1,
                    'sumber'     => 'system',
                ]);

            // Tambah foreign key + unique constraint
            Schema::table('bank_sampah_kategori', function (Blueprint $table) {
                $table->foreign('config_id')
                    ->references('id')->on('config')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->unique(['config_id', 'nama']);
            });
        }

        // ============================================================
        // 2. TABEL BANK_SAMPAH_SAMPAH
        // ============================================================
        if (Schema::hasTable('bank_sampah_sampah')) {
            Schema::table('bank_sampah_sampah', function (Blueprint $table) use ($desaId) {
                // Tambah config_id
                if (! Schema::hasColumn('bank_sampah_sampah', 'config_id')) {
                    $table->integer('config_id')->nullable()->after('id');
                }
                // Tambah is_default
                if (! Schema::hasColumn('bank_sampah_sampah', 'is_default')) {
                    $table->tinyInteger('is_default')->default(0)->after('aktif');
                }
                // Tambah sumber
                if (! Schema::hasColumn('bank_sampah_sampah', 'sumber')) {
                    $table->string('sumber', 20)->default('system')->after('is_default');
                }
            });

            // Set config_id untuk data existing (seeder lama)
            DB::table('bank_sampah_sampah')
                ->whereNull('config_id')
                ->update([
                    'config_id'  => $desaId,
                    'is_default' => 1,
                    'sumber'     => 'system',
                ]);

            // Tambah foreign key + unique constraint
            Schema::table('bank_sampah_sampah', function (Blueprint $table) {
                $table->foreign('config_id')
                    ->references('id')->on('config')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->unique(['config_id', 'kategori_id', 'nama']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bank_sampah_sampah')) {
            Schema::table('bank_sampah_sampah', function (Blueprint $table) {
                $table->dropForeign(['config_id']);
                $table->dropIndex(['config_id', 'kategori_id', 'nama']);
                $table->dropColumn(['config_id', 'is_default', 'sumber']);
            });
        }

        if (Schema::hasTable('bank_sampah_kategori')) {
            Schema::table('bank_sampah_kategori', function (Blueprint $table) {
                $table->dropForeign(['config_id']);
                $table->dropIndex(['config_id', 'nama']);
                $table->dropColumn(['config_id', 'is_default', 'sumber']);
            });
        }
    }
};
