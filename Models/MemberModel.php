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
use App\Models\Penduduk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MemberModel extends BaseModel
{
    use HasUserstamp;

    use ConfigId;

    protected $table = 'bank_sampah_member';

    /**
     * issue #61: whitelist $fillable — bukan $guarded kosong.
     * userstamps (created_by/updated_by) dikelola trait inti.
     */
    protected $fillable = [
        'config_id', 'penduduk_id', 'no_member', 'nama', 'alamat', 'no_hp', 'lokasi_id', 'aktif'
    // issue #61: saldo & poin diubah via method khusus (tambahSaldo/kurangiSaldo), bukan mass-assignment,
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'saldo'      => 'decimal:2',
        'poin'       => 'integer',
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
    ];

    /**
     * Auto-generate UUID on creation
     */
    protected static function booted(): void
    {
        static::creating(function (MemberModel $member) {
            if (empty($member->uuid)) {
                $member->uuid = Str::uuid()->toString();
            }
        });
    }

    /**
     * Relasi ke lokasi
     */
    public function lokasi()
    {
        return $this->belongsTo(LokasiModel::class, 'lokasi_id');
    }

    /**
     * Relasi ke data penduduk OpenSID (tweb_penduduk)
     */
    public function penduduk()
    {
        return $this->belongsTo(Penduduk::class, 'penduduk_id');
    }

    /**
     * Relasi ke transaksi
     */
    public function transaksi()
    {
        return $this->hasMany(TransaksiModel::class, 'member_id');
    }

    /**
     * Relasi ke reward
     */
    public function reward()
    {
        return $this->hasMany(RewardModel::class, 'member_id');
    }


    /**
     * Scope: hanya yang aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', 1);
    }

    /**
     * Generate nomor member unik per desa (thread-safe)
     */
    public static function generateNoMember(string $prefix = 'BS'): string
    {
        $configId = identitas('id');

        return DB::transaction(function () use ($prefix, $configId) {
            $last = static::where('config_id', $configId)
                ->where('no_member', 'LIKE', $prefix . '-%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;
            if ($last) {
                $lastNumber = (int) substr($last->no_member, strlen($prefix) + 1);
                $nextNumber = $lastNumber + 1;
            }

            return $prefix . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Tambah saldo (atomic)
     */
    public function tambahSaldo(float $jumlah): bool
    {
        $affected = DB::table($this->table)
            ->where('id', $this->id)
            ->increment('saldo', $jumlah);

        if ($affected > 0) {
            $this->refresh();
            return true;
        }
        return false;
    }

    /**
     * Kurangi saldo (atomic + check)
     */
    public function kurangiSaldo(float $jumlah): bool
    {
        $affected = DB::table($this->table)
            ->where('id', $this->id)
            ->where('saldo', '>=', $jumlah)
            ->decrement('saldo', $jumlah);

        if ($affected > 0) {
            $this->refresh();
            return true;
        }
        return false;
    }

    /**
     * Tambah poin (atomic)
     */
    public function tambahPoin(int $jumlah): bool
    {
        $affected = DB::table($this->table)
            ->where('id', $this->id)
            ->increment('poin', $jumlah);

        if ($affected > 0) {
            $this->refresh();
            return true;
        }
        return false;
    }

    /**
     * Tukar poin (atomic + check)
     */
    public function tukarPoin(int $jumlah): bool
    {
        $affected = DB::table($this->table)
            ->where('id', $this->id)
            ->where('poin', '>=', $jumlah)
            ->decrement('poin', $jumlah);

        if ($affected > 0) {
            $this->refresh();
            return true;
        }
        return false;
    }

    /**
     * Reset UUID (regenerate)
     */
    public function resetUuid(): string
    {
        $this->uuid = Str::uuid()->toString();
        $this->save();
        return $this->uuid;
    }
}
