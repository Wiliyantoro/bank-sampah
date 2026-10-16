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
        if (! Schema::hasTable('bank_sampah_sampah')) {
            Schema::create('bank_sampah_sampah', static function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('kategori_id');
                $table->string('nama', 150);
                $table->string('satuan', 20)->default('kg');
                $table->decimal('harga_beli', 15, 2)->default(0);
                $table->decimal('harga_jual', 15, 2)->default(0);
                $table->decimal('stok', 10, 2)->default(0);
                $table->tinyInteger('aktif')->default(1);
                $table->timestamps();

                $table->foreign('kategori_id')
                    ->references('id')
                    ->on('bank_sampah_kategori')
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
        Schema::dropIfExists('bank_sampah_sampah');
    }
};
