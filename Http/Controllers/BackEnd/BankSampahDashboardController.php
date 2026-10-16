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
use Illuminate\Support\Facades\Cache;
use Modules\BankSampah\Models\BankSampahMember;
use Modules\BankSampah\Models\BankSampahTransaksi;
use Modules\BankSampah\Models\BankSampahSampah;
use Modules\BankSampah\Models\BankSampahLokasi;

class BankSampahDashboardController extends Controller
{
    public function index(Request $request)
    {
        // BS-017: param bulan/tahun divalidasi sebelum dipakai di cache key/DB
        [$bulan, $tahun] = $this->periodeQuery($request);
        $configId = identitas('id');
        $cacheKey = "banksampah_dash_{$configId}_{$tahun}{$bulan}";

        $data = Cache::remember($cacheKey, 300, function () use ($configId, $bulan, $tahun) {
            $totalMember = BankSampahMember::desa()->aktif()->count();

            $transaksiHariIni = BankSampahTransaksi::desa()
                ->whereDate('created_at', today())->count();

            $setoranBulan = BankSampahTransaksi::desa()
                ->where('tipe', 'setor')
                ->whereMonth('created_at', $bulan)
                ->whereYear('created_at', $tahun)
                ->sum('total');

            $tarikanBulan = BankSampahTransaksi::desa()
                ->where('tipe', 'tarik')
                ->whereMonth('created_at', $bulan)
                ->whereYear('created_at', $tahun)
                ->sum('total');

            $saldoKas = $setoranBulan - $tarikanBulan;
            $totalStok = BankSampahSampah::desa()->sum('stok');
            $totalLokasi = BankSampahLokasi::desa()->aktif()->count();

            $transaksiTerakhir = BankSampahTransaksi::desa()
                ->with('member')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return compact(
                'totalMember', 'transaksiHariIni', 'setoranBulan',
                'tarikanBulan', 'saldoKas', 'totalStok', 'totalLokasi',
                'transaksiTerakhir'
            );
        });

        // Grafik data (tidak di-cache karena date range dinamis)
        $grafikData = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = date('m', strtotime("-{$i} months"));
            $y = date('Y', strtotime("-{$i} months"));

            $setor = BankSampahTransaksi::desa()
                ->where('tipe', 'setor')
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', $y)
                ->sum('total');

            $tarik = BankSampahTransaksi::desa()
                ->where('tipe', 'tarik')
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', $y)
                ->sum('total');

            $grafikData[] = [
                'bulan' => date('M Y', strtotime("{$y}-{$m}-01")),
                'setor' => (float) $setor,
                'tarik' => (float) $tarik,
            ];
        }

        return view('banksampah::backend.dashboard', array_merge($data, compact(
            'grafikData', 'bulan', 'tahun'
        )));
    }

    /**
     * Validasi & normalisasi param bulan/tahun (BS-017).
     * Input garbage diblok sebelum menyentuh cache key / whereMonth/whereYear.
     */
    protected function periodeQuery(Request $request): array
    {
        $data = $request->validate([
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|between:2000,2100',
        ]);

        return [(int) ($data['bulan'] ?? date('m')), (int) ($data['tahun'] ?? date('Y'))];
    }
}
