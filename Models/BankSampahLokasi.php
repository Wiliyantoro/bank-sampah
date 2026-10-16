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

class BankSampahLokasi extends BaseModel
{
    protected $table = 'bank_sampah_lokasi';

    protected $fillable = [
        'config_id',
        'nama',
        'kode',
        'alamat',
        'penanggung_jawab',
        'no_hp',
        'aktif',
        'is_default',
        'sumber',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Relasi ke member
     */
    public function member()
    {
        return $this->hasMany(BankSampahMember::class, 'lokasi_id');
    }

    /**
     * Relasi ke transaksi
     */
    public function transaksi()
    {
        return $this->hasMany(BankSampahTransaksi::class, 'lokasi_id');
    }

    /**
     * Scope: hanya data desa yang sedang login
     */
    public function scopeDesa($query)
    {
        return $query->where('config_id', identitas('id'));
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
