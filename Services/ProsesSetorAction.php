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
use Modules\BankSampah\Models\RewardModel;
use Modules\BankSampah\Models\SampahModel;
use Modules\BankSampah\Models\TransaksiModel;

/**
 * Action: proses transaksi setor (issue #65).
 *
 * Seluruh logika bisnis setor dipindah dari TransaksiController agar
 * controller hanya mengerjakan validasi, flash message, notifikasi,
 * dan redirect. DB::transaction(fn) dipertahankan (issue #61) —
 * return dini diganti throw Exception agar rollback pasti jalan.
 */
class ProsesSetorAction
{
    /**
     * @param array $items [['sampah_id' => int, 'berat' => float], ...]
     *
     * @return array{0: MemberModel, 1: float, 2: float, 3: int, 4: TransaksiModel}
     *               [member, total, totalBerat, poinDidapat, transaksi]
     *
     * @throws Exception jenis sampah tidak ditemukan / gagal DB
     */
    public function handle(int $memberId, ?int $lokasiId, array $items, ?string $keterangan = null): array
    {
        return DB::transaction(function () use ($memberId, $lokasiId, $items, $keterangan) {
            $member = MemberModel::desa()->findOrFail($memberId);

            // #105: nasabah nonaktif tidak boleh bertransaksi
            if (! $member->aktif) {
                throw new Exception('member_nonaktif');
            }

            $total   = 0;
            $details = [];

            // issue #63: prefetch semua sampah sekali (keyBy), bukan
            // findOrFail per item — N+1 hilang.
            $ids = array_unique(array_column($items, 'sampah_id'));
            $sampahMap = SampahModel::desa()
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $sampah = $sampahMap->get($item['sampah_id'])
                    ?? throw new Exception('jenis sampah tidak ditemukan');

                // #105: jenis sampah nonaktif tidak boleh ditransaksikan
                if (! $sampah->aktif) {
                    throw new Exception('sampah_nonaktif');
                }

                $subtotal = $item['berat'] * $sampah->harga_beli;
                $total += $subtotal;

                $details[] = [
                    'sampah_id' => $item['sampah_id'],
                    'berat'     => $item['berat'],
                    'harga'     => $sampah->harga_beli,
                    'subtotal'  => $subtotal,
                ];
            }

            $totalBerat  = array_sum(array_column($items, 'berat'));
            $poinPerKg   = (int) (setting('bank_sampah_poin_per_kg') ?: 10);
            $poinDidapat = (int) ($totalBerat * $poinPerKg);

            $transaksi = TransaksiModel::create([
                'no_transaksi' => TransaksiModel::generateNoTransaksi(),
                'member_id'    => $member->id,
                'lokasi_id'    => $lokasiId ?: null,
                'tipe'         => TipeTransaksiEnum::SETOR->value,
                'total'        => $total,
                'bayar'        => $total,
                'poin'         => $poinDidapat,
                'keterangan'   => $keterangan,
                'user_id'      => auth()->id(),
            ]);

            // issue #63: batch insert detail + update stok dari map
            // yang sudah di-prefetch (tanpa query tambahan per item).
            $now = now();
            DB::table('bank_sampah_transaksi_detail')->insert(array_map(
                static function (array $detail) use ($transaksi, $now): array {
                    return $detail + [
                        'transaksi_id' => $transaksi->id,
                        'config_id'    => identitas('id'),
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                },
                $details
            ));

            foreach ($details as $detail) {
                $sampahMap->get($detail['sampah_id'])->tambahStok($detail['berat']);
            }

            $member->tambahSaldo($total);

            if (setting('bank_sampah_reward_aktif') == 1 && $poinDidapat > 0) {
                $member->tambahPoin($poinDidapat);

                RewardModel::create([
                    'member_id'    => $member->id,
                    'transaksi_id' => $transaksi->id,
                    'tipe'         => 'add',
                    'poin'         => $poinDidapat,
                    'keterangan'   => 'Poin dari transaksi setor #' . $transaksi->no_transaksi,
                ]);
            }

            return [$member, $total, $totalBerat, $poinDidapat, $transaksi];
        });
    }
}
