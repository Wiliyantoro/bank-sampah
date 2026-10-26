<?php

/*
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 */

defined('BASEPATH') || exit('No direct script access allowed');

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Modules\BankSampah\Models\LokasiModel;
use Modules\BankSampah\Models\MemberModel;
use Modules\BankSampah\Models\SampahModel;
use Modules\BankSampah\Models\TransaksiModel;

class DashboardController extends AdminModulController
{
    public $moduleName    = 'BankSampah';
    public $modul_ini     = 'bank-sampah';
    public $sub_modul_ini = 'bank-sampah-dashboard';
    public $kategori_pengaturan = 'Bank Sampah';
    public $aliasController   = 'bank-sampah.dashboard';

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    public function index()
    {
        // BS-017: param bulan/tahun divalidasi sebelum dipakai di cache key/DB
        [$bulan, $tahun] = $this->periodeQuery();
        $configId = identitas('id');
        $cacheKey = "banksampah_dash_{$configId}_{$tahun}{$bulan}";

        $data = Cache::remember($cacheKey, 300, function () use ($configId, $bulan, $tahun) {
            $totalMember = MemberModel::query()->aktif()->count();

            $transaksiHariIni = TransaksiModel::query()
                ->whereDate('created_at', today())->count();

            $setoranBulan = TransaksiModel::query()
                ->where('tipe', 'setor')
                ->whereBetween('created_at', $this->rentangBulan($bulan, $tahun))
                ->sum('total');

            $tarikanBulan = TransaksiModel::query()
                ->where('tipe', 'tarik')
                ->whereBetween('created_at', $this->rentangBulan($bulan, $tahun))
                ->sum('total');


            $saldoKas = $setoranBulan - $tarikanBulan;
            $totalStok = SampahModel::query()->sum('stok');
            $totalLokasi = LokasiModel::query()->aktif()->count();

            $transaksiTerakhir = TransaksiModel::query()
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

        // Grafik data (12 bulan terakhir) — BS-024: 1 query agregasi + cache 5 menit
        // (sebelumnya 2 query/bulan = 24 query per render, tanpa cache)
        $grafikData = Cache::remember("banksampah_dash_grafik_{$configId}", 300, function () {
            $mulai = now()->subMonths(11)->startOfMonth();

            $rows = TransaksiModel::query()
                ->select('created_at', 'tipe', 'total')
                ->where('created_at', '>=', $mulai)
                ->get();

            // Bucket per bulan-tipe dilakukan di PHP agar portabel lintas DB
            $map = [];
            foreach ($rows as $row) {
                $ym = $row->created_at->format('Y-m');
                $key = $ym . '_' . $row->tipe;
                $map[$key] = ($map[$key] ?? 0) + (float) $row->total;
            }

            $grafik = [];
            for ($i = 11; $i >= 0; $i--) {
                $m = date('m', strtotime("-{$i} months"));
                $y = date('Y', strtotime("-{$i} months"));
                $ym = "{$y}-{$m}";

                $grafik[] = [
                    'bulan' => date('M Y', strtotime("{$y}-{$m}-01")),
                    'setor' => $map[$ym . '_setor'] ?? 0.0,
                    'tarik' => $map[$ym . '_tarik'] ?? 0.0,
                ];
            }

            return $grafik;
        });

        return view('banksampah::backend.dashboard', array_merge($data, compact(
            'grafikData', 'bulan', 'tahun'
        )));
    }

    /**
     * Validasi & normalisasi param bulan/tahun (BS-017).
     * Input garbage diblok sebelum menyentuh cache key / whereMonth/whereYear.
     */
    protected function periodeQuery(): array
    {
        $data = request()->validate([
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|between:2000,2100',
        ]);

        return [(int) ($data['bulan'] ?? date('m')), (int) ($data['tahun'] ?? date('Y'))];
    }

    /**
     * Batas [mulai, akhir] bulan tertentu (issue #64).
     * whereBetween sargable — memakai index (config_id, created_at),
     * pengganti whereMonth/whereYear yang tidak bisa pakai index.
     */
    protected function rentangBulan(int $bulan, int $tahun): array
    {
        $mulai  = Carbon::create($tahun, $bulan, 1, 0, 0, 0)->startOfDay();
        $akhir  = (clone $mulai)->endOfMonth();

        return [$mulai, $akhir];
    }
}
