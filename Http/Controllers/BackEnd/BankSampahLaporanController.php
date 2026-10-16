<?php

/*
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 */

namespace Modules\BankSampah\Http\Controllers\BackEnd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\BankSampah\Models\BankSampahTransaksi;
use Modules\BankSampah\Models\BankSampahTransaksiDetail;
use Modules\BankSampah\Models\BankSampahSampah;
use Modules\BankSampah\Models\BankSampahMember;
use Modules\BankSampah\Models\BankSampahLokasi;

class BankSampahLaporanController extends Controller
{
    /**
     * Halaman laporan (filtered per desa)
     */
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        // Rekap harian
        $rekapHarian = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%s-%s-%02d', $tahun, $bulan, $day);

            $setor = BankSampahTransaksi::desa()->where('tipe', 'setor')
                ->whereDate('created_at', $date)
                ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total), 0) as nominal')
                ->first();

            $tarik = BankSampahTransaksi::desa()->where('tipe', 'tarik')
                ->whereDate('created_at', $date)
                ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total), 0) as nominal')
                ->first();

            $rekapHarian[] = [
                'tanggal'       => $date,
                'nama_hari'     => date('l', strtotime($date)),
                'setor_jumlah'  => $setor->jumlah,
                'setor_nominal' => $setor->nominal,
                'tarik_jumlah'  => $tarik->jumlah,
                'tarik_nominal' => $tarik->nominal,
            ];
        }

        // Rekap jenis sampah
        $rekapSampah = BankSampahTransaksiDetail::selectRaw('
                sampah_id,
                SUM(berat) as total_berat,
                SUM(subtotal) as total_nilai
            ')
            ->whereHas('transaksi', function ($q) use ($bulan, $tahun) {
                $q->desa()
                  ->whereMonth('created_at', $bulan)
                  ->whereYear('created_at', $tahun);
            })
            ->with('sampah.kategori')
            ->groupBy('sampah_id')
            ->get();

        // Rekap per lokasi
        $rekapLokasi = BankSampahTransaksi::desa()->selectRaw('
                lokasi_id,
                tipe,
                COUNT(*) as jumlah,
                COALESCE(SUM(total), 0) as nominal
            ')
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->groupBy('lokasi_id', 'tipe')
            ->with('lokasi')
            ->get();

        // Total
        $totalSetor = BankSampahTransaksi::desa()->where('tipe', 'setor')
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total), 0) as nominal')
            ->first();

        $totalTarik = BankSampahTransaksi::desa()->where('tipe', 'tarik')
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total), 0) as nominal')
            ->first();

        return view('banksampah::backend.laporan.index', compact(
            'rekapHarian', 'rekapSampah', 'rekapLokasi',
            'totalSetor', 'totalTarik', 'bulan', 'tahun'
        ));
    }

    /**
     * Export laporan ke PDF (HTML print view)
     */
    public function exportPdf(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $config = config('banksampah');

        $rekapHarian = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%s-%s-%02d', $tahun, $bulan, $day);

            $setor = BankSampahTransaksi::desa()->where('tipe', 'setor')
                ->whereDate('created_at', $date)
                ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total), 0) as nominal')
                ->first();

            $tarik = BankSampahTransaksi::desa()->where('tipe', 'tarik')
                ->whereDate('created_at', $date)
                ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total), 0) as nominal')
                ->first();

            $rekapHarian[] = [
                'tanggal'       => $date,
                'nama_hari'     => date('l', strtotime($date)),
                'setor_jumlah'  => $setor->jumlah,
                'setor_nominal' => $setor->nominal,
                'tarik_jumlah'  => $tarik->jumlah,
                'tarik_nominal' => $tarik->nominal,
            ];
        }

        $rekapSampah = BankSampahTransaksiDetail::selectRaw('
                sampah_id,
                SUM(berat) as total_berat,
                SUM(subtotal) as total_nilai
            ')
            ->whereHas('transaksi', function ($q) use ($bulan, $tahun) {
                $q->desa()
                  ->whereMonth('created_at', $bulan)
                  ->whereYear('created_at', $tahun);
            })
            ->with('sampah.kategori')
            ->groupBy('sampah_id')
            ->get();

        $totalSetor = BankSampahTransaksi::desa()->where('tipe', 'setor')
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total), 0) as nominal')
            ->first();

        $totalTarik = BankSampahTransaksi::desa()->where('tipe', 'tarik')
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total), 0) as nominal')
            ->first();

        return view('banksampah::backend.laporan.cetak_pdf', compact(
            'rekapHarian', 'rekapSampah', 'totalSetor', 'totalTarik',
            'bulan', 'tahun', 'config'
        ));
    }

    /**
     * Export ke CSV (with injection prevention)
     */
    public function exportCsv(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $transaksi = BankSampahTransaksi::desa()->with('member')
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->orderBy('created_at', 'asc')
            ->get();

        $filename = "transaksi_bank_sampah_{$bulan}_{$tahun}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transaksi) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['No Transaksi', 'Tanggal', 'No Member', 'Nama', 'Tipe', 'Total', 'Keterangan']);

            foreach ($transaksi as $trx) {
                fputcsv($file, [
                    $trx->no_transaksi,
                    $trx->created_at->format('d/m/Y H:i'),
                    $trx->member->no_member ?? '',
                    sanitize_csv_field($trx->member->nama ?? ''),
                    ucfirst($trx->tipe),
                    $trx->total,
                    sanitize_csv_field($trx->keterangan ?? ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
