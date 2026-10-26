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

use Modules\BankSampah\Traits\HasUserstamp;
use App\Models\BaseModel;
use App\Traits\ConfigId;

class LokasiModel extends BaseModel
{
    use HasUserstamp;

    use ConfigId;

    protected $table = 'bank_sampah_lokasi';

    /**
     * issue #61: whitelist $fillable — bukan $guarded kosong.
     * userstamps (created_by/updated_by) dikelola trait inti.
     */
    protected $fillable = [
        'config_id', 'nama', 'kode', 'alamat', 'penanggung_jawab', 'no_hp', 'is_default', 'sumber', 'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'is_default' => 'boolean',
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
    ];

    /**
     * Relasi ke member
     */
    public function member()
    {
        return $this->hasMany(MemberModel::class, 'lokasi_id');
    }

    /**
     * Relasi ke transaksi
     */
    public function transaksi()
    {
        return $this->hasMany(TransaksiModel::class, 'lokasi_id');
    }


    /**
     * Scope: hanya yang aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', 1);
    }

    /**
     * Cek apakah ini data bawaan sistem
     */
    public function isDefault(): bool
    {
        return $this->is_default === true || $this->is_default == 1;
    }
}
