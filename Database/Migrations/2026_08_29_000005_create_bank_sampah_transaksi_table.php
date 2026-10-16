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
        if (! Schema::hasTable('bank_sampah_transaksi')) {
            Schema::create('bank_sampah_transaksi', static function (Blueprint $table): void {
                $table->integer('id', true);
                $table->string('no_transaksi', 30)->unique();
                $table->integer('member_id');
                $table->integer('lokasi_id')->nullable();
                $table->enum('tipe', ['setor', 'tarik']);
                $table->decimal('total', 15, 2)->default(0);
                $table->decimal('bayar', 15, 2)->default(0);
                $table->decimal('potongan', 15, 2)->default(0);
                $table->integer('poin')->default(0);
                $table->text('keterangan')->nullable();
                $table->integer('user_id')->nullable();
                $table->timestamps();

                $table->foreign('member_id')
                    ->references('id')
                    ->on('bank_sampah_member')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->foreign('lokasi_id')
                    ->references('id')
                    ->on('bank_sampah_lokasi')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_sampah_transaksi');
    }
};
