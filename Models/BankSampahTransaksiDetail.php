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

namespace Modules\BankSampah\Models;

class BankSampahTransaksiDetail extends BaseModel
{
    protected $table = 'bank_sampah_transaksi_detail';

    protected $fillable = [
        'transaksi_id',
        'sampah_id',
        'berat',
        'harga',
        'subtotal',
    ];

    protected $casts = [
        'berat'    => 'decimal:2',
        'harga'    => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Relasi ke transaksi
     */
    public function transaksi()
    {
        return $this->belongsTo(BankSampahTransaksi::class, 'transaksi_id');
    }

    /**
     * Relasi ke jenis sampah
     */
    public function sampah()
    {
        return $this->belongsTo(BankSampahSampah::class, 'sampah_id');
    }
}
