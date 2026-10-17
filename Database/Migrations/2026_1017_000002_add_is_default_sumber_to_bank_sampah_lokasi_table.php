<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
 * BS-041: kolom is_default/sumber tabel bank_sampah_lokasi tidak pernah dibuat
 *
 * Controller (BankSampahLokasiController::store) dan view badge
 * bawaan/desa (Views/backend/lokasi/index.blade.php) memakai kolom
 * is_default & sumber — tetapi hanya migrasi kategori & sampah
 * (2026_09_01_000001) yang membuatnya. Akibatnya pada instalasi fresh
 * menambah Lokasi/TPS gagal HTTP 500:
 * "Unknown column 'is_default' in 'field list'".
 *
 * Guard introspeksi ala #30: ALTER hanya jalan bila kolomnya belum ada,
 * sehingga re-run Migrasi_module CRI menjadi no-op.
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('bank_sampah_lokasi')) {
            return;
        }

        if (! Schema::hasColumn('bank_sampah_lokasi', 'is_default')) {
            Schema::table('bank_sampah_lokasi', static function (Blueprint $table): void {
                $table->tinyInteger('is_default')->default(0)->after('aktif');
            });
        }

        if (! Schema::hasColumn('bank_sampah_lokasi', 'sumber')) {
            Schema::table('bank_sampah_lokasi', static function (Blueprint $table): void {
                $table->string('sumber', 20)->default('desa')->after('is_default');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('bank_sampah_lokasi')) {
            return;
        }

        if (Schema::hasColumn('bank_sampah_lokasi', 'sumber')) {
            Schema::table('bank_sampah_lokasi', static function (Blueprint $table): void {
                $table->dropColumn('sumber');
            });
        }

        if (Schema::hasColumn('bank_sampah_lokasi', 'is_default')) {
            Schema::table('bank_sampah_lokasi', static function (Blueprint $table): void {
                $table->dropColumn('is_default');
            });
        }
    }
};
