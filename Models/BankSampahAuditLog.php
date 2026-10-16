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

class BankSampahAuditLog extends BaseModel
{
    protected $table = 'bank_sampah_audit_log';

    protected $fillable = [
        'config_id',
        'event',
        'user_id',
        'ip_address',
        'user_agent',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (BankSampahAuditLog $log) {
            if (empty($log->config_id)) {
                $log->config_id = identitas('id');
            }
        });
    }

    /**
     * Scope: filter by event type
     */
    public function scopeEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope: filter by date range
     */
    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Scope: recent logs
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope: hanya data desa yang sedang login
     */
    public function scopeDesa($query)
    {
        return $query->where('config_id', identitas('id'));
    }
}
