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

use Carbon\Carbon;
use Modules\BankSampah\Enums\TipeTransaksiEnum;
use Modules\BankSampah\Models\TransaksiDetailModel;
use Modules\BankSampah\Models\TransaksiModel;

/**
 * Service: agregasi laporan bank sampah (issue #65).
 *
 * Logic `dataRekap()` dipindah dari LaporanController agar
 * controller hanya menyiapkan view; index dan exportPdf share
 * satu sumber rekap (BS-038 tetap: satu query agregasi per kelompok).
 */
class LaporanBankSampah
{
    /**
     * Rekap lengkap satu bulan untuk index() dan exportPdf().
     */
    public function dataRekap(int $bulan, int $tahun): array
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
        $rentang     = $this->rentangBulan($bulan, $tahun);

        // Rekap harian — BS-024: satu query agregasi, dimap ke kalender di PHP
        // issue #64: whereBetween sargable (pakai idx_transaksi_config_tanggal)
        $rekapRows = TransaksiModel::query()
            ->selectRaw('DATE(created_at) as tgl, tipe, COUNT(*) as jumlah, COALESCE(SUM(total), 0) as nominal')
            ->whereBetween('created_at', $rentang)
            ->groupBy('tgl', 'tipe')
            ->get();

        $rekapIndex = [];
        foreach ($rekapRows as $row) {
            $rekapIndex[$row->tgl . '_' . $row->tipe] = $row;
        }

        $rekapHarian = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%s-%s-%02d', $tahun, $bulan, $day);

            $setor = $rekapIndex[$date . '_setor'] ?? null;
            $tarik = $rekapIndex[$date . '_tarik'] ?? null;

            $rekapHarian[] = [
                'tanggal'       => $date,
                'nama_hari'     => bank_sampah_hari_indonesia($date),
                'setor_jumlah'  => (int) ($setor->jumlah ?? 0),
                'setor_nominal' => (float) ($setor->nominal ?? 0),
                'tarik_jumlah'  => (int) ($tarik->jumlah ?? 0),
                'tarik_nominal' => (float) ($tarik->nominal ?? 0),
            ];
        }

        // Rekap jenis sampah
        $rekapSampah = TransaksiDetailModel::selectRaw('
                sampah_id,
                SUM(berat) as total_berat,
                SUM(subtotal) as total_nilai
            ')
            ->whereHas('transaksi', function ($q) use ($rentang) {
                $q->whereBetween('created_at', $rentang);
            })
            ->with('sampah.kategori')
            ->groupBy('sampah_id')
            ->get();

        // Rekap per lokasi
        $rekapLokasi = TransaksiModel::query()->selectRaw('
                lokasi_id,
                tipe,
                COUNT(*) as jumlah,
                COALESCE(SUM(total), 0) as nominal
            ')
            ->whereBetween('created_at', $rentang)
            ->groupBy('lokasi_id', 'tipe')
            ->with('lokasi')
            ->get();

        // Total
        $totalSetor = TransaksiModel::query()->where('tipe', TipeTransaksiEnum::SETOR->value)
            ->whereBetween('created_at', $rentang)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total), 0) as nominal')
            ->first();

        $totalTarik = TransaksiModel::query()->where('tipe', TipeTransaksiEnum::TARIK->value)
            ->whereBetween('created_at', $rentang)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total), 0) as nominal')
            ->first();

        return compact(
            'rekapHarian', 'rekapSampah', 'rekapLokasi', 'totalSetor', 'totalTarik'
        );
    }

    /**
     * Validasi & normalisasi param bulan/tahun (BS-017) — bersama untuk
     * DashboardController & LaporanController (issue #65: periodeQuery duplikat).
     */
    public static function periodeQuery(): array
    {
        $data = request()->validate([
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|between:2000,2100',
        ]);

        return [(int) ($data['bulan'] ?? date('m')), (int) ($data['tahun'] ?? date('Y'))];
    }

    /**
     * Batas [mulai, akhir] bulan tertentu (issue #64).
     * whereBetween sargable — pengganti whereMonth/whereYear
     * yang tidak bisa memakai index.
     */
    public function rentangBulan(int $bulan, int $tahun): array
    {
        $mulai = Carbon::create($tahun, $bulan, 1, 0, 0, 0)->startOfDay();
        $akhir = (clone $mulai)->endOfMonth();

        return [$mulai, $akhir];
    }
}
