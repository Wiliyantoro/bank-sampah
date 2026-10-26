<?php

/*
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 */

namespace Modules\BankSampah\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Isi created_by/updated_by otomatis (issue #66).
 *
 * Kolom datang dari macro migrasi inti timesWithUserstamps(), tapi kolom
 * saja tidak cukup — tanpa trait ini kedua kolom selalu NULL.
 * Di konteks CLI (seeder/tinker) auth() kosong: nilai dibiarkan NULL,
 * bukan error.
 */
trait HasUserstamp
{
    public static function bootHasUserstamp(): void
    {
        static::creating(function ($model): void {
            if (! $model->isDirty('created_by') && Auth::check()) {
                $model->created_by = Auth::id();
            }

            if (! $model->isDirty('updated_by') && Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model): void {
            if (! $model->isDirty('updated_by') && Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}
