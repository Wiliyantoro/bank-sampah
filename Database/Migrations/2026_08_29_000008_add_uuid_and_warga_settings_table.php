<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
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
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambah kolom UUID ke tabel member
        if (Schema::hasTable('bank_sampah_member')) {
            Schema::table('bank_sampah_member', function (Blueprint $table) {
                if (! Schema::hasColumn('bank_sampah_member', 'uuid')) {
                    $table->string('uuid', 36)->unique()->after('id');
                }
                if (! Schema::hasColumn('bank_sampah_member', 'pin')) {
                    $table->string('pin', 6)->nullable()->after('no_hp');
                }
            });

            // Generate UUID untuk member yang sudah ada
            $members = DB::table('bank_sampah_member')->whereNull('uuid')->get();
            foreach ($members as $member) {
                DB::table('bank_sampah_member')
                    ->where('id', $member->id)
                    ->update(['uuid' => sprintf(
                        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                        mt_rand(0, 0xffff),
                        mt_rand(0, 0x0fff) | 0x4000,
                        mt_rand(0, 0x3fff) | 0x8000,
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                    )]);
            }
        }

        // Tabel pengaturan dashboard warga
        if (! Schema::hasTable('bank_sampah_warga_settings')) {
            Schema::create('bank_sampah_warga_settings', static function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('config_id');
                $table->tinyInteger('warga_aktif')->default(1);
                $table->tinyInteger('show_saldo')->default(1);
                $table->tinyInteger('show_riwayat')->default(1);
                $table->tinyInteger('show_poin')->default(1);
                $table->tinyInteger('require_pin')->default(1);
                $table->integer('max_riwayat')->default(10);
                $table->text('pesan_selamat_datang')->nullable();
                $table->text('pesan_footer')->nullable();
                $table->timestamps();

                $table->foreign('config_id')
                    ->references('id')
                    ->on('config')
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
        Schema::dropIfExists('bank_sampah_warga_settings');

        Schema::table('bank_sampah_member', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'pin']);
        });
    }
};
