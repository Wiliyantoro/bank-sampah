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
use Illuminate\Support\Facades\DB;

class TransaksiModel extends BaseModel
{
    use ConfigId;

    protected $table = 'bank_sampah_transaksi';

    protected $guarded = [];

    protected $casts = [
        'total'      => 'decimal:2',
        'bayar'      => 'decimal:2',
        'potongan'   => 'decimal:2',
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
     * Relasi ke lokasi
     */
    public function lokasi()
    {
        return $this->belongsTo(LokasiModel::class, 'lokasi_id');
    }

    /**
     * Relasi ke detail transaksi
     */
    public function detail()
    {
        return $this->hasMany(TransaksiDetailModel::class, 'transaksi_id');
    }

    /**
     * Scope: hanya data desa yang sedang login
     */
    public function scopeDesa($query)
    {
        return $query->where('config_id', identitas('id'));
    }

    /**
     * Generate nomor transaksi unik per desa (thread-safe)
     * Format: TRX-YYYYMMDD-XXXX
     */
    public static function generateNoTransaksi(): string
    {
        $configId = identitas('id');
        $date = date('Ymd');
        $prefix = 'TRX-' . $date . '-';

        return DB::transaction(function () use ($configId, $prefix) {
            $last = static::where('config_id', $configId)
                ->where('no_transaksi', 'LIKE', $prefix . '%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;
            if ($last) {
                // BS-022: parse semua digit setelah prefix (bukan -4) agar
                // nomor urut > 9999 (5+ digit) tetap terhitung tanpa duplikat —
                // str_pad(min 4) tidak memangkas angka yang lebih panjang
                $lastNumber = (int) substr($last->no_transaksi, strlen($prefix));
                $nextNumber = $lastNumber + 1;
            }

            return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }
}
