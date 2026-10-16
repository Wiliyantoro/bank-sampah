<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 * @link      https://github.com/OpenSID/OpenSID
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
        if (! Schema::hasTable('bank_sampah_transaksi_detail')) {
            Schema::create('bank_sampah_transaksi_detail', static function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('transaksi_id');
                $table->integer('sampah_id');
                $table->decimal('berat', 10, 2)->default(0);
                $table->decimal('harga', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->timestamps();

                $table->foreign('transaksi_id')
                    ->references('id')
                    ->on('bank_sampah_transaksi')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->foreign('sampah_id')
                    ->references('id')
                    ->on('bank_sampah_sampah')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_sampah_transaksi_detail');
    }
};
