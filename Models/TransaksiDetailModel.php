<?php

/*
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 */

namespace Modules\BankSampah\Models;

use App\Models\BaseModel;

class TransaksiDetailModel extends BaseModel
{
    protected $table = 'bank_sampah_transaksi_detail';

    protected $guarded = [];

    protected $casts = [
        'berat'    => 'decimal:2',
        'harga'    => 'decimal:2',
        'subtotal' => 'decimal:2',
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
    ];

    /**
     * Relasi ke transaksi
     */
    public function transaksi()
    {
        return $this->belongsTo(TransaksiModel::class, 'transaksi_id');
    }

    /**
     * Relasi ke jenis sampah
     */
    public function sampah()
    {
        return $this->belongsTo(SampahModel::class, 'sampah_id');
    }
}
