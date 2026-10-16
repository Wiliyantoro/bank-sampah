<?php

/*
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        $desaId = identitas('id');
        $tables = ['bank_sampah_lokasi', 'bank_sampah_member', 'bank_sampah_transaksi', 'bank_sampah_reward', 'bank_sampah_audit_log'];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (! Schema::hasColumn($table, 'config_id')) {
                Schema::table($table, function (Blueprint $tbl) {
                    $tbl->integer('config_id')->nullable()->after('id');
                });
            }

            DB::table($table)
                ->whereNull('config_id')
                ->update(['config_id' => $desaId]);

            if (! $this->hasForeignKey($table, 'config_id')) {
                Schema::table($table, function (Blueprint $tbl) {
                    $tbl->foreign('config_id')
                        ->references('id')->on('config')
                        ->onUpdate('cascade')->onDelete('cascade');
                });
            }
        }

        // Unique constraints per desa (#30): dijalankan hanya bila belum ada,
        // agar re-run Migrasi_module CRI tidak gagal errno-1061.
        foreach ($this->uniqueColumns() as $table => $columns) {
            if (Schema::hasTable($table) && ! $this->hasUnique($table, $columns)) {
                Schema::table($table, function (Blueprint $tbl) use ($columns) {
                    $tbl->unique($columns);
                });
            }
        }

        // Indexes performa (#30): cek nama index dulu, CREATE INDEX tidak
        // boleh diulang pada index yang sudah eksis.
        foreach ($this->performanceIndexes() as $name => [$table, $columns]) {
            if (! Schema::hasTable($table) || $this->hasIndex($table, $name)) {
                continue;
            }

            DB::statement(sprintf(
                'CREATE INDEX %s ON %s (%s)',
                $name,
                $table,
                implode(', ', $columns)
            ));
        }
    }

    public function down(): void
    {
        $tables = ['bank_sampah_lokasi', 'bank_sampah_member', 'bank_sampah_transaksi', 'bank_sampah_reward', 'bank_sampah_audit_log'];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            // Index performa dan unique komposit dibersihkan dulu (#30):
            // MariaDB menolak drop kolom yang masih dipakai index.
            foreach ($this->performanceIndexes() as $name => [$indexTable, $columns]) {
                if ($indexTable === $table && $this->hasIndex($table, $name)) {
                    Schema::table($table, function (Blueprint $tbl) use ($name) {
                        $tbl->dropIndex($name);
                    });
                }
            }

            if (isset($this->uniqueColumns()[$table])) {
                $columns = $this->uniqueColumns()[$table];

                if ($this->hasUnique($table, $columns)) {
                    Schema::table($table, function (Blueprint $tbl) use ($columns) {
                        $tbl->dropUnique($columns);
                    });
                }
            }

            if ($this->hasForeignKey($table, 'config_id')) {
                Schema::table($table, function (Blueprint $tbl) {
                    $tbl->dropForeign(['config_id']);
                });
            }

            if (Schema::hasColumn($table, 'config_id')) {
                Schema::table($table, function (Blueprint $tbl) {
                    $tbl->dropColumn('config_id');
                });
            }
        }
    }

    /**
     * Peta unique komposit per desa (#30): nama tabel => daftar kolom.
     */
    private function uniqueColumns(): array
    {
        return [
            'bank_sampah_lokasi'    => ['config_id', 'kode'],
            'bank_sampah_member'    => ['config_id', 'no_member'],
            'bank_sampah_transaksi' => ['config_id', 'no_transaksi'],
        ];
    }

    /**
     * Peta index performa (#30): nama index => [tabel, kolom].
     */
    private function performanceIndexes(): array
    {
        return [
            'idx_lokasi_config_active'   => ['bank_sampah_lokasi', ['config_id', 'aktif']],
            'idx_member_config_active'   => ['bank_sampah_member', ['config_id', 'aktif']],
            'idx_transaksi_config_date'  => ['bank_sampah_transaksi', ['config_id', 'created_at']],
            'idx_transaksi_config_tipe'  => ['bank_sampah_transaksi', ['config_id', 'tipe', 'created_at']],
            'idx_reward_config_member'   => ['bank_sampah_reward', ['config_id', 'member_id']],
            'idx_audit_config_event'     => ['bank_sampah_audit_log', ['config_id', 'event']],
        ];
    }

    /**
     * Cek foreign key pada kolom tertentu sudah ada (#30).
     */
    private function hasForeignKey(string $table, string $column): bool
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if (array_values($foreignKey['columns'] ?? []) === [$column]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cek unique index dengan susunan kolom tertentu sudah ada (#30).
     */
    private function hasUnique(string $table, array $columns): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['unique'] ?? false)
                && array_values($index['columns'] ?? []) === $columns) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cek index bernama tertentu sudah ada (#30).
     */
    private function hasIndex(string $table, string $name): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['name'] ?? '') === $name) {
                return true;
            }
        }

        return false;
    }
};
