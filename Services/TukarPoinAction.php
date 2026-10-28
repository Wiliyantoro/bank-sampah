<?php

/*
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 */

namespace Modules\BankSampah\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\BankSampah\Models\MemberModel;
use Modules\BankSampah\Models\RewardModel;

/**
 * Action: tukar poin nasabah (issue #65).
 */
class TukarPoinAction
{
    /**
     * @throws Exception poin_tidak_cukup / gagal DB
     */
    public function handle(MemberModel $member, int $poin, string $keterangan): void
    {
        DB::transaction(function () use ($member, $poin, $keterangan) {
            // #105: nasabah nonaktif tidak boleh menukar poin
            if (! $member->aktif) {
                throw new Exception('member_nonaktif');
            }

            if (! $member->tukarPoin($poin)) {
                throw new Exception('poin_tidak_cukup');
            }

            RewardModel::create([
                'member_id'  => $member->id,
                'tipe'       => 'tukar',
                'poin'       => $poin,
                'keterangan' => $keterangan,
            ]);
        });
    }
}
