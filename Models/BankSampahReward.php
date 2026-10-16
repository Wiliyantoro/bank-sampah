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

class BankSampahReward extends BaseModel
{
    protected $table = 'bank_sampah_reward';

    protected $fillable = [
        'config_id',
        'member_id',
        'transaksi_id',
        'tipe',
        'poin',
        'keterangan',
    ];

    protected $casts = [
        'poin' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (BankSampahReward $reward) {
            if (empty($reward->config_id)) {
                $reward->config_id = identitas('id');
            }
        });
    }

    /**
     * Relasi ke member
     */
    public function member()
    {
        return $this->belongsTo(BankSampahMember::class, 'member_id');
    }

    /**
     * Relasi ke transaksi
     */
    public function transaksi()
    {
        return $this->belongsTo(BankSampahTransaksi::class, 'transaksi_id');
    }

    /**
     * Scope: hanya data desa yang sedang login
     */
    public function scopeDesa($query)
    {
        return $query->where('config_id', identitas('id'));
    }
}
