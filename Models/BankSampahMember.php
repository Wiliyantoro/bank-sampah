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

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class BankSampahMember extends BaseModel
{
    protected $table = 'bank_sampah_member';

    protected $fillable = [
        'config_id',
        'uuid',
        'no_member',
        'nama',
        'alamat',
        'no_hp',
        'pin',
        'penduduk_id',
        'lokasi_id',
        'saldo',
        'poin',
        'aktif',
    ];

    protected $hidden = [
        'pin',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
        'poin'  => 'integer',
    ];

    /**
     * Auto-generate UUID on creation
     */
    protected static function booted(): void
    {
        static::creating(function (BankSampahMember $member) {
            if (empty($member->uuid)) {
                $member->uuid = Str::uuid()->toString();
            }
            if (empty($member->config_id)) {
                $member->config_id = identitas('id');
            }
        });
    }

    /**
     * Relasi ke lokasi
     */
    public function lokasi()
    {
        return $this->belongsTo(BankSampahLokasi::class, 'lokasi_id');
    }

    /**
     * Relasi ke data penduduk OpenSID (tweb_penduduk)
     */
    public function penduduk()
    {
        return $this->belongsTo('App\Models\TwebPenduduk', 'penduduk_id');
    }

    /**
     * Relasi ke transaksi
     */
    public function transaksi()
    {
        return $this->hasMany(BankSampahTransaksi::class, 'member_id');
    }

    /**
     * Relasi ke reward
     */
    public function reward()
    {
        return $this->hasMany(BankSampahReward::class, 'member_id');
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
     * Set PIN (hashed)
     */
    public function setPin(string $pin): bool
    {
        $this->pin = Hash::make($pin);
        return $this->save();
    }

    /**
     * Verify PIN
     */
    public function verifyPin(string $pin): bool
    {
        if (empty($this->pin)) {
            return true;
        }
        return Hash::check($pin, $this->pin);
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

    /**
     * Generate shareable link
     */
    public function getShareLink(): string
    {
        return url('bank-sampah/warga/share/' . $this->uuid);
    }
}
