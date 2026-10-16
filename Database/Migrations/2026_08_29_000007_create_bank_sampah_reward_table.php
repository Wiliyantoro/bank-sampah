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
        if (! Schema::hasTable('bank_sampah_reward')) {
            Schema::create('bank_sampah_reward', static function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('member_id');
                $table->integer('transaksi_id')->nullable();
                $table->enum('tipe', ['add', 'tukar']);
                $table->integer('poin');
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();

                $table->foreign('member_id')
                    ->references('id')
                    ->on('bank_sampah_member')
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
        Schema::dropIfExists('bank_sampah_reward');
    }
};
