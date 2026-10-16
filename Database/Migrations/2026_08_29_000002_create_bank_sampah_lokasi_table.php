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
        if (! Schema::hasTable('bank_sampah_lokasi')) {
            Schema::create('bank_sampah_lokasi', static function (Blueprint $table): void {
                $table->integer('id', true);
                $table->string('nama', 150);
                $table->string('kode', 20)->unique();
                $table->text('alamat')->nullable();
                $table->string('penanggung_jawab', 150)->nullable();
                $table->string('no_hp', 20)->nullable();
                $table->tinyInteger('aktif')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_sampah_lokasi');
    }
};
