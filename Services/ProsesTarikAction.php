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
use Modules\BankSampah\Enums\TipeTransaksiEnum;
use Modules\BankSampah\Models\MemberModel;
use Modules\BankSampah\Models\TransaksiModel;

/**
 * Action: proses transaksi tarik saldo (issue #65).
 *
 * Throw Exception('saldo_tidak_cukup') untuk kondisi domain —
 * controller memetakan ke pesan UI.
 */
class ProsesTarikAction
{
    /**
     * @return array{0: MemberModel, 1: TransaksiModel} [member, transaksi]
     *
     * @throws Exception saldo_tidak_cukup / gagal DB
     */
    public function handle(int $memberId, float $nominal, ?string $keterangan = null): array
    {
        return DB::transaction(function () use ($memberId, $nominal, $keterangan) {
            $member = MemberModel::desa()->findOrFail($memberId);

            // #105: nasabah nonaktif tidak boleh bertransaksi
            if (! $member->aktif) {
                throw new Exception('member_nonaktif');
            }

            if ($member->saldo < $nominal) {
                throw new Exception('saldo_tidak_cukup');
            }

            $transaksi = TransaksiModel::create([
                'no_transaksi' => TransaksiModel::generateNoTransaksi(),
                'member_id'    => $member->id,
                'tipe'         => TipeTransaksiEnum::TARIK->value,
                'total'        => $nominal,
                'bayar'        => $nominal,
                'keterangan'   => $keterangan,
                'user_id'      => auth()->id(),
            ]);

            if (! $member->kurangiSaldo($nominal)) {
                throw new Exception('saldo_tidak_cukup');
            }

            return [$member, $transaksi];
        });
    }
}
