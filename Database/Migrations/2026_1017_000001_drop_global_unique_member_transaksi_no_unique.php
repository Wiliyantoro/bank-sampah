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
     * BS-035: index UNIQUE GLOBAL pada no_member / no_transaksi (dibuat by
     * migrasi awal) bertentangan dengan generator nomor PER DESA dan UNIQUE
     * composite (config_id, ...) yang lebih tepat. Pada instalasi multi-desa,
     * desa kedua yang baru mulai menghasilkan BS-00001 / TRX-YYYYMMDD-0001
     * yang sama dan ditolak index global dengan dup key (HTTP 500) terus
     * menerus. Index global dilepas; UNIQUE composite per-desa tetap dipakai.
     */
    public function up(): void
    {
        $this->dropGlobalUniqueSingleColumn('bank_sampah_member', 'no_member');
        $this->dropGlobalUniqueSingleColumn('bank_sampah_transaksi', 'no_transaksi');
    }

    /**
     * Drop index UNIQUE yang terbentuk hanya dari 1 kolom (bukan composite).
     * Introspeksi via Schema::getIndexes() agar aman lintas driver (mysql/sqlite).
     */
    protected function dropGlobalUniqueSingleColumn(string $table, string $column): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach (Schema::getIndexes($table) as $index) {
            if (($index['unique'] ?? false) && ($index['columns'] ?? []) === [$column]) {
                Schema::table($table, function (Blueprint $tbl) use ($index): void {
                    $tbl->dropUnique($index['name']);
                });
            }
        }
    }

    public function down(): void
    {
        // Disediakan tidak dipulihkan (no-op): memulihkan UNIQUE global justru
        // mengembalikan bug multi-desa ini (BS-035).
    }
};
