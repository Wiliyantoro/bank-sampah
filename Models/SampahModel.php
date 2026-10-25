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

class SampahModel extends BaseModel
{
    use ConfigId;

    protected $table = 'bank_sampah_sampah';

    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok'       => 'decimal:2',
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
    ];

    /**
     * Relasi ke kategori
     */
    public function kategori()
    {
        return $this->belongsTo(KategoriModel::class, 'kategori_id');
    }

    /**
     * Relasi ke detail transaksi
     */
    public function transaksiDetail()
    {
        return $this->hasMany(TransaksiDetailModel::class, 'sampah_id');
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
     * Tambah stok — atomik (UPDATE stok = stok + $jumlah), anti lost update (BS-010)
     */
    public function tambahStok(float $jumlah): bool
    {
        $affected = static::query()
            ->whereKey($this->getKey())
            ->increment('stok', $jumlah);

        // Sinkronkan nilai in-memory dari DB (refresh() no-op untuk model
        // yang belum persist — baca eksplisit agar selalu akurat)
        if ($affected > 0) {
            $stokBaru = static::query()->whereKey($this->getKey())->value('stok');
            if ($stokBaru !== null) {
                $this->stok = $stokBaru;
                $this->exists = true;
            }
        }

        return $affected > 0;
    }

    /**
     * Kurangi stok — atomik dengan guard stok >= jumlah (BS-010).
     * Cek ketersediaan dan pengurangan dilakukan dalam 1 statement agar
     * dua transaksi tarik konkuren tidak bisa membuat stok minus.
     */
    public function kurangiStok(float $jumlah): bool
    {
        $affected = static::query()
            ->whereKey($this->getKey())
            ->where('stok', '>=', $jumlah)
            ->decrement('stok', $jumlah);

        if ($affected > 0) {
            $stokBaru = static::query()->whereKey($this->getKey())->value('stok');
            if ($stokBaru !== null) {
                $this->stok = $stokBaru;
                $this->exists = true;
            }
        }

        return $affected > 0;
    }
}
