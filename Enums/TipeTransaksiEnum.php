<?php

/*
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 */

namespace Modules\BankSampah\Enums;

/**
 * Tipe transaksi bank sampah (issue #66).
 *
 * Nilai case sengaja sama dengan isi kolom `tipe` di DB. Enum dipakai
 * sebagai konstanta bernama di kode PHP; cast kolom tetap 'string'
 * karena banyak view membandingkan `$t->tipe === 'setor'` langsung.
 */
enum TipeTransaksiEnum: string
{
    case SETOR = 'setor';
    case TARIK = 'tarik';

    public static function label(self $tipe): string
    {
        return $tipe === self::SETOR ? 'Setor' : 'Tarik';
    }
}
