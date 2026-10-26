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
        Schema::table('bank_sampah_transaksi', function (Blueprint $table) {
            $table->index(['config_id', 'created_at'], 'idx_transaksi_config_tanggal');
            $table->index(['config_id', 'tipe', 'created_at'], 'idx_transaksi_config_tipe_tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('bank_sampah_transaksi', function (Blueprint $table) {
            $table->dropIndex('idx_transaksi_config_tanggal');
            $table->dropIndex('idx_transaksi_config_tipe_tanggal');
        });
    }
};
