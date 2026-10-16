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

class BankSampahSampah extends BaseModel
{
    protected $table = 'bank_sampah_sampah';

    protected $fillable = [
        'config_id',
        'kategori_id',
        'nama',
        'satuan',
        'harga_beli',
        'harga_jual',
        'stok',
        'aktif',
        'is_default',
        'sumber',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok'       => 'decimal:2',
    ];

    /**
     * Relasi ke kategori
     */
    public function kategori()
    {
        return $this->belongsTo(BankSampahKategori::class, 'kategori_id');
    }

    /**
     * Relasi ke detail transaksi
     */
    public function transaksiDetail()
    {
        return $this->hasMany(BankSampahTransaksiDetail::class, 'sampah_id');
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

    /**
     * Tambah stok
     */
    public function tambahStok(float $jumlah): bool
    {
        $this->stok += $jumlah;
        return $this->save();
    }

    /**
     * Kurangi stok
     */
    public function kurangiStok(float $jumlah): bool
    {
        if ($this->stok < $jumlah) {
            return false;
        }
        $this->stok -= $jumlah;
        return $this->save();
    }
}
