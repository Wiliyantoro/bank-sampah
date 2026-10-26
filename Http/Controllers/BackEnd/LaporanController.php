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

use Modules\BankSampah\Services\LaporanBankSampah;
use Modules\BankSampah\Enums\TipeTransaksiEnum;
use Modules\BankSampah\Models\TransaksiDetailModel;
use Modules\BankSampah\Models\TransaksiModel;

class LaporanController extends AdminModulController
{
    public $moduleName    = 'BankSampah';
    public $modul_ini     = 'bank-sampah';
    public $sub_modul_ini = 'bank-sampah-laporan';
    public $kategori_pengaturan = 'Bank Sampah';
    public $aliasController   = 'bank-sampah.laporan';

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
     * Rekap bulanan bersama untuk index() dan exportPdf() — logic ada di
     * Services/LaporanBankSampah (issue #65).
     */
    protected function dataRekap(int $bulan, int $tahun): array
    {
        return app(LaporanBankSampah::class)->dataRekap($bulan, $tahun);
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

        return $config + ['periode_label' => bank_sampah_bulan_indonesia($bulan) . ' ' . $tahun];
    }

    /**
     * Export ke CSV (with injection prevention)
     */
    public function exportCsv()
    {
        [$bulan, $tahun] = $this->periodeQuery();

        $transaksi = TransaksiModel::query()->with('member')
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
                    bank_sampah_sanitize_csv_field($trx->member->nama ?? ''),
                    ucfirst($trx->tipe),
                    $trx->total,
                    bank_sampah_sanitize_csv_field($trx->keterangan ?? ''),
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
        return LaporanBankSampah::periodeQuery();
    }

}
