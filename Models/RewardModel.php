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
use App\Traits\ConfigId;

class RewardModel extends BaseModel
{
    use ConfigId;

    protected $table = 'bank_sampah_reward';

    protected $guarded = [];

    protected $casts = [
        'poin'       => 'integer',
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
    ];

    /**
     * Relasi ke member
     */
    public function member()
    {
        return $this->belongsTo(MemberModel::class, 'member_id');
    }

    /**
     * Relasi ke transaksi
     */
    public function transaksi()
    {
        return $this->belongsTo(TransaksiModel::class, 'transaksi_id');
    }

}
