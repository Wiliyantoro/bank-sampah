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

use Modules\BankSampah\Models\TransaksiDetailModel;
use Modules\BankSampah\Models\TransaksiModel;

class LaporanController extends AdminModulController
{
    public $moduleName    = 'BankSampah';
    public $modul_ini     = 'bank-sampah';
    public $sub_modul_ini = 'bank-sampah-laporan';
    public $kategori_pengaturan = 'Bank Sampah';

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    /**
     * Halaman laporan (filtered per desa)
     */
    public function index()
    {
        [$bulan, $tahun] = $this->periodeQuery();

        return view('banksampah::backend.laporan.index', array_merge(
            $this->dataRekap($bulan, $tahun),
            compact('bulan', 'tahun')
        ));
    }

    /**
     * Rekap bulanan bersama untuk index() dan exportPdf() — BS-038:
     * satu query agregasi per bulan (dulu: 2 query/hari = max 62 query)
     */
    protected function dataRekap(int $bulan, int $tahun): array
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        // Rekap harian — BS-024: satu query agregasi, dimap ke kalender di PHP
        $rekapRows = TransaksiModel::desa()
            ->selectRaw('DATE(created_at) as tgl, tipe, COUNT(*) as jumlah, COALESCE(SUM(total), 0) as nominal')
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
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
                'nama_hari'     => hari_indonesia($date),
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
            ->whereHas('transaksi', function ($q) use ($bulan, $tahun) {
                $q->desa()
                  ->whereMonth('created_at', $bulan)
                  ->whereYear('created_at', $tahun);
            })
            ->with('sampah.kategori')
            ->groupBy('sampah_id')
            ->get();

        // Rekap per lokasi
        $rekapLokasi = TransaksiModel::desa()->selectRaw('
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
        $totalSetor = TransaksiModel::desa()->where('tipe', 'setor')
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total), 0) as nominal')
            ->first();

        $totalTarik = TransaksiModel::desa()->where('tipe', 'tarik')
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total), 0) as nominal')
            ->first();

        return compact('rekapHarian', 'rekapSampah', 'rekapLokasi', 'totalSetor', 'totalTarik');
    }

    /**
     * Export laporan ke PDF (BS-042: PDF asli via TCPDF bawaan core;
     * fallback HTML print-view bila TCPDF tidak tersedia di instalasi).
     */
    public function exportPdf()
    {
        [$bulan, $tahun] = $this->periodeQuery();
        $config = $this->kopLaporan($bulan, $tahun);

        extract($this->dataRekap($bulan, $tahun));

        $html = view('banksampah::backend.laporan.cetak_pdf', compact(
            'rekapHarian', 'rekapSampah', 'totalSetor', 'totalTarik',
            'bulan', 'tahun', 'config'
        ))->render();

        $filename = "laporan_bank_sampah_{$bulan}_{$tahun}.pdf";

        // BS-042: dokumen PDF asli (Content-Type application/pdf) — dulu
        // hanya HTML print-view yang diunduh sebagai file .html.
        try {
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('OpenSID - Modul Bank Sampah');
            $pdf->SetAuthor($config['nama']);
            $pdf->SetTitle("Laporan Bank Sampah {$bulan}/{$tahun}");
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(12, 10, 12, true);
            $pdf->SetAutoPageBreak(true, 14);
            $pdf->setFont('helvetica', '', 10);
            $pdf->AddPage();
            $pdf->writeHTML($html, true, false, true, false, '');

            return response($pdf->Output($filename, 'S'), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Cache-Control'       => 'max-age=0, private, must-revalidate',
            ]);
        } catch (Throwable $e) {
            // TCPDF tidak tersedia / gagal konversi — jangan buntu:
            // degrade ke HTML print-view (perilaku lama) dan catat sebabnya.
            report($e);

            return response()->make($html, 200, [
                'Content-Type' => 'text/html; charset=utf-8',
            ]);
        }
    }

    /**
     * Kop laporan (BS-042): konfigurasi modul dulu; bila kosong, pakai profil
     * desa (identitas) — dulu "Alamat: | Telp:" selalu kosong karena memang
     * hanya membaca setting Pengaturan Aplikasi yang bawaannya kosong.
     */
    protected function kopLaporan(int $bulan, int $tahun): array
    {
        // BS-042: pengaturan modul dulu (Pengaturan Aplikasi, item 5); bila
        // kosong, pakai profil desa (identitas) — dulu "Alamat: | Telp:"
        // selalu kosong karena memang hanya membaca config dengan bawaan kosong.
        $config           = [];
        $config['nama']   = setting('bank_sampah_nama')   ?: (identitas('nama_desa') ?: 'BANK SAMPAH');
        $config['alamat'] = setting('bank_sampah_alamat') ?: (identitas('alamat_kantor') ?: '-');
        $config['no_hp']  = setting('bank_sampah_no_hp')  ?: (identitas('telepon') ?: '-');

        return $config + ['periode_label' => bulan_indonesia($bulan) . ' ' . $tahun];
    }

    /**
     * Export ke CSV (with injection prevention)
     */
    public function exportCsv()
    {
        [$bulan, $tahun] = $this->periodeQuery();

        $transaksi = TransaksiModel::desa()->with('member')
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

    /**
     * Validasi & normalisasi param bulan/tahun (BS-017).
     * Input garbage diblok sebelum menyentuh cal_days_in_month / whereDate / header CSV.
     */
    protected function periodeQuery(): array
    {
        $data = request()->validate([
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|between:2000,2100',
        ]);

        return [(int) ($data['bulan'] ?? date('m')), (int) ($data['tahun'] ?? date('Y'))];
    }
}
