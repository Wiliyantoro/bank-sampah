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

class BankSampahKategori extends BaseModel
{
    protected $table = 'bank_sampah_kategori';

    protected $fillable = [
        'config_id',
        'nama',
        'keterangan',
        'aktif',
        'is_default',
        'sumber',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Relasi ke jenis sampah
     */
    public function sampah()
    {
        return $this->hasMany(BankSampahSampah::class, 'kategori_id');
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
     * Scope: hanya data bawaan (seeder)
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', 1);
    }

    /**
     * Scope: hanya data custom desa
     */
    public function scopeCustom($query)
    {
        return $query->where('is_default', 0);
    }

    /**
     * Cek apakah ini data bawaan sistem
     */
    public function isDefault(): bool
    {
        return $this->is_default === true || $this->is_default == 1;
    }

    /**
     * Cek apakah ini data custom desa
     */
    public function isCustom(): bool
    {
        return ! $this->isDefault();
    }
}
