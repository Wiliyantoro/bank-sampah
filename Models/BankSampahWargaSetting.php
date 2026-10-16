<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 *
 */

namespace Modules\BankSampah\Models;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

class BankSampahWargaSetting extends BaseModel
{
    protected $table = 'bank_sampah_warga_settings';

    protected $fillable = [
        'config_id',
        'warga_aktif',
        'show_saldo',
        'show_riwayat',
        'show_poin',
        'require_pin',
        'max_riwayat',
        'pesan_selamat_datang',
        'pesan_footer',
    ];

    protected $casts = [
        'warga_aktif'        => 'boolean',
        'show_saldo'         => 'boolean',
        'show_riwayat'       => 'boolean',
        'show_poin'          => 'boolean',
        'require_pin'        => 'boolean',
        'max_riwayat'        => 'integer',
    ];

    /**
     * TTL cache settings (detik)
     */
    public const CACHE_TTL = 300;

    /**
     * Key cache settings per desa (BS-011: satu sumber kebenaran)
     */
    public static function cacheKey(int $configId): string
    {
        return 'banksampah_warga_settings_' . $configId;
    }

    /**
     * Hapus cache settings desa ini
     */
    public function invalidateCache(): void
    {
        Cache::forget(static::cacheKey((int) $this->config_id));
    }

    protected static function booted(): void
    {
        // BS-011: setiap perubahan settings harus langsung efektif,
        // termasuk require_pin/warga_aktif yang berdan game keamanan.
        static::saved(fn (self $settings) => $settings->invalidateCache());
        static::deleted(fn (self $settings) => $settings->invalidateCache());
    }

    /**
     * Get or create settings for config.
     *
     * BS-027: dengan UNIQUE(config_id), dua request yang sama-sama kehilangan
     * race dapat saling memanggil insert — yang kalah QueryException dan cukup
     * membaca ulang baris yang sudah dibuat pemenangnya.
     */
    public static function getSettings(int $configId): self
    {
        try {
            return static::firstOrCreate(
                ['config_id' => $configId],
                [
                    'warga_aktif'        => true,
                    'show_saldo'         => true,
                    'show_riwayat'       => true,
                    'show_poin'          => true,
                    'require_pin'        => true,
                    'max_riwayat'        => 10,
                    'pesan_selamat_datang' => 'Selamat datang di dashboard Bank Sampah desa kami!',
                    'pesan_footer'       => 'Terima kasih sudah menjaga lingkungan!',
                ]
            );
        } catch (QueryException $e) {
            return static::where('config_id', $configId)->firstOrFail();
        }
    }
}
