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
        if (! Schema::hasTable('bank_sampah_member')) {
            Schema::create('bank_sampah_member', static function (Blueprint $table): void {
                $table->integer('id', true);
                $table->string('no_member', 20)->unique();
                $table->string('nama', 150);
                $table->text('alamat')->nullable();
                $table->string('no_hp', 20)->nullable();
                $table->integer('penduduk_id')->nullable();
                $table->integer('lokasi_id')->nullable();
                $table->decimal('saldo', 15, 2)->default(0);
                $table->integer('poin')->default(0);
                $table->tinyInteger('aktif')->default(1);
                $table->timestamps();

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
        Schema::dropIfExists('bank_sampah_member');
    }
};
