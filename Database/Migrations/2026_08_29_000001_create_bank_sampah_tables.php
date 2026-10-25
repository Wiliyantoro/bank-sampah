<?php

/*
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * Satu migrasi create untuk semua tabel modul (consolidated):
 * config_id + FK + unique per-desa + index performa dibuat SEJAK
 * Schema::create — tidak ada lagi 12 file migrasi tambalan ALTER
 * (issue #43 item 4: config_id sejak create, migrasi gabungan).
 *
 * D-001: tabel bank_sampah_warga_settings DIHAPUS dan kolom `pin`
 * member tidak pernah dibuat — autentikasi warga memakai sesi
 * Layanan Mandiri inti (NIK + PIN di tweb_penduduk).
 *
 * BS-035: UNIQUE hanya composite per-desa (config_id, ...) — TIDAK ada
 * unique global single-kolom pada no_member/no_transaksi/kode.
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
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
        if (! Schema::hasTable('bank_sampah_kategori')) {
            Schema::create('bank_sampah_kategori', static function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('config_id');
                $table->string('nama', 100);
                $table->text('keterangan')->nullable();
                $table->tinyInteger('is_default')->default(0);
                $table->string('sumber', 20)->default('system');
                $table->tinyInteger('aktif')->default(1);
                $table->timestamps();

                $table->unique(['config_id', 'nama']);
                $table->foreign('config_id')
                    ->references('id')->on('config')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('bank_sampah_sampah')) {
            Schema::create('bank_sampah_sampah', static function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('config_id');
                $table->integer('kategori_id');
                $table->string('nama', 150);
                $table->string('satuan', 20)->default('kg');
                $table->decimal('harga_beli', 15, 2)->default(0);
                $table->decimal('harga_jual', 15, 2)->default(0);
                $table->decimal('stok', 10, 2)->default(0);
                $table->tinyInteger('is_default')->default(0);
                $table->string('sumber', 20)->default('system');
                $table->tinyInteger('aktif')->default(1);
                $table->timestamps();

                $table->unique(['config_id', 'kategori_id', 'nama']);
                $table->foreign('config_id')
                    ->references('id')->on('config')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('kategori_id')
                    ->references('id')->on('bank_sampah_kategori')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('bank_sampah_lokasi')) {
            Schema::create('bank_sampah_lokasi', static function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('config_id');
                $table->string('nama', 150);
                $table->string('kode', 20);
                $table->text('alamat')->nullable();
                $table->string('penanggung_jawab', 150)->nullable();
                $table->string('no_hp', 20)->nullable();
                $table->tinyInteger('is_default')->default(0);
                $table->string('sumber', 20)->default('desa');
                $table->tinyInteger('aktif')->default(1);
                $table->timestamps();

                $table->unique(['config_id', 'kode']);
                $table->index(['config_id', 'aktif'], 'idx_lokasi_config_active');
                $table->foreign('config_id')
                    ->references('id')->on('config')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('bank_sampah_member')) {
            Schema::create('bank_sampah_member', static function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('config_id');
                $table->string('uuid', 36);
                $table->string('no_member', 20);
                $table->string('nama', 150);
                $table->text('alamat')->nullable();
                $table->string('no_hp', 20)->nullable();
                $table->integer('penduduk_id')->nullable();
                $table->integer('lokasi_id')->nullable();
                $table->decimal('saldo', 15, 2)->default(0);
                $table->integer('poin')->default(0);
                $table->tinyInteger('aktif')->default(1);
                $table->timestamps();

                $table->unique(['config_id', 'no_member']);
                $table->index(['config_id', 'aktif'], 'idx_member_config_active');
                $table->foreign('config_id')
                    ->references('id')->on('config')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('lokasi_id')
                    ->references('id')->on('bank_sampah_lokasi')
                    ->onUpdate('cascade')->onDelete('set null');
            });
        }

        if (! Schema::hasTable('bank_sampah_transaksi')) {
            Schema::create('bank_sampah_transaksi', static function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('config_id');
                $table->string('no_transaksi', 30);
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

                $table->unique(['config_id', 'no_transaksi']);
                $table->index(['config_id', 'created_at'], 'idx_transaksi_config_date');
                $table->index(['config_id', 'tipe', 'created_at'], 'idx_transaksi_config_tipe');
                $table->foreign('config_id')
                    ->references('id')->on('config')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('member_id')
                    ->references('id')->on('bank_sampah_member')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('lokasi_id')
                    ->references('id')->on('bank_sampah_lokasi')
                    ->onUpdate('cascade')->onDelete('set null');
            });
        }

        if (! Schema::hasTable('bank_sampah_transaksi_detail')) {
            Schema::create('bank_sampah_transaksi_detail', static function (Blueprint $table): void {
                // TANPA config_id: tabel detail mengikuti induknya lewat FK transaksi_id.
                $table->integer('id', true);
                $table->integer('transaksi_id');
                $table->integer('sampah_id');
                $table->decimal('berat', 10, 2)->default(0);
                $table->decimal('harga', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->timestamps();

                $table->foreign('transaksi_id')
                    ->references('id')->on('bank_sampah_transaksi')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('sampah_id')
                    ->references('id')->on('bank_sampah_sampah')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('bank_sampah_reward')) {
            Schema::create('bank_sampah_reward', static function (Blueprint $table): void {
                $table->integer('id', true);
                $table->integer('config_id');
                $table->integer('member_id');
                $table->integer('transaksi_id')->nullable();
                $table->enum('tipe', ['add', 'tukar']);
                $table->integer('poin');
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();

                $table->index(['config_id', 'member_id'], 'idx_reward_config_member');
                $table->foreign('config_id')
                    ->references('id')->on('config')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('member_id')
                    ->references('id')->on('bank_sampah_member')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('bank_sampah_audit_log')) {
            Schema::create('bank_sampah_audit_log', static function (Blueprint $table): void {
                $table->id();
                $table->integer('config_id');
                $table->string('event', 100);
                $table->integer('user_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->json('data')->nullable();
                $table->timestamps();

                $table->index('event');
                $table->index('created_at');
                $table->index(['event', 'created_at']);
                $table->index(['config_id', 'event'], 'idx_audit_config_event');
                $table->foreign('config_id')
                    ->references('id')->on('config')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ([
            'bank_sampah_audit_log',
            'bank_sampah_reward',
            'bank_sampah_transaksi_detail',
            'bank_sampah_transaksi',
            'bank_sampah_member',
            'bank_sampah_lokasi',
            'bank_sampah_sampah',
            'bank_sampah_kategori',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
