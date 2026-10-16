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
     * Get or create settings for config
     */
    public static function getSettings(int $configId): self
    {
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
    }
}
