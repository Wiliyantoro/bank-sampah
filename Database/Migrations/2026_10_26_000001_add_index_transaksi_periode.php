<?php

/*
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 */

defined('BASEPATH') || exit('No direct script access allowed');

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * issue #64: index komposit agar filter periode (whereBetween created_at)
 * dan filter tipe tidak full-scan.
 */
return new class extends Migration
{
    public function up(): void
    {
        // #88: migrasi ALTER harus idempotent — runner migrasi modul
        // terbundel tidak menyimpan state, jadi file ini dieksekusi ulang
        // pada SETIAP update core. Instalasi yang sudah menerapkan fix #64
        // (atau bundel lama) sudah punya index bernama sama → ALTER polos
        // gagal errno 1061 Duplicate key name.
        $indexes = collect(Schema::getIndexes('bank_sampah_transaksi'))->pluck('name');

        Schema::table('bank_sampah_transaksi', function (Blueprint $table) use ($indexes) {
            if (! $indexes->contains('idx_transaksi_config_tanggal')) {
                $table->index(['config_id', 'created_at'], 'idx_transaksi_config_tanggal');
            }
            if (! $indexes->contains('idx_transaksi_config_tipe_tanggal')) {
                $table->index(['config_id', 'tipe', 'created_at'], 'idx_transaksi_config_tipe_tanggal');
            }

            // #89: create migration (2026_08_29_000001) sudah membuat index
            // identik dengan nama lain (_date/_tipe) — tanpa ini fresh install
            // membawa 4 index untuk 2 definisi kolom yang sama (overhead tulis
            // dua kali). Nama _tanggal/_tipe_tanggal dijadikan satu-satunya.
            if ($indexes->contains('idx_transaksi_config_date')) {
                $table->dropIndex('idx_transaksi_config_date');
            }
            if ($indexes->contains('idx_transaksi_config_tipe')) {
                $table->dropIndex('idx_transaksi_config_tipe');
            }
        });
    }

    public function down(): void
    {
        // #88: down() juga di-guard — dropIndex pada index yang tidak ada
        // akan gagal sama kerasnya.
        $indexes = collect(Schema::getIndexes('bank_sampah_transaksi'))->pluck('name');

        Schema::table('bank_sampah_transaksi', function (Blueprint $table) use ($indexes) {
            if ($indexes->contains('idx_transaksi_config_tanggal')) {
                $table->dropIndex('idx_transaksi_config_tanggal');
            }
            if ($indexes->contains('idx_transaksi_config_tipe_tanggal')) {
                $table->dropIndex('idx_transaksi_config_tipe_tanggal');
            }

            // #89: balikin index _date/_tipe milik create migration agar
            // down() konsisten dengan skema pra-migrasi.
            if (! $indexes->contains('idx_transaksi_config_date')) {
                $table->index(['config_id', 'created_at'], 'idx_transaksi_config_date');
            }
            if (! $indexes->contains('idx_transaksi_config_tipe')) {
                $table->index(['config_id', 'tipe', 'created_at'], 'idx_transaksi_config_tipe');
            }
        });
    }
};
