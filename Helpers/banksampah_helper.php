<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 *
 */

/*
 * CATATAN PENTING:
 * Jangan pernah mendefinisikan ulang helper global OpenSID seperti ci_route(),
 * site_url(), dsb. Helper modul ini dimuat lebih awal dari helper inti (donjo-app/helpers)
 * pada versi tertentu, sehingga bila mendefinisikan ulang ci_route(), seluruh halaman
 * inti OpenSID yang memanggil ci_route('controller.method') akan menghasilkan URL
 * bertitik (mis. surat_master.datatables) yang tidak dikenali router -> 404 ->
 * DataTables "Ajax error". Gunakan url()/route() milik Laravel di view modul.
 */

if (! function_exists('format_rupiah')) {
    /**
     * Format angka ke Rupiah
     */
    function format_rupiah($angka): string
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

if (! function_exists('format_berat')) {
    /**
     * Format berat dengan satuan
     */
    function format_berat($berat, $satuan = 'kg'): string
    {
        return number_format($berat, 2, ',', '.') . ' ' . $satuan;
    }
}

if (! function_exists('safe_like')) {
    /**
     * Escape wildcard SQL untuk dipakai di klausa LIKE '%...%' (BS-031).
     *
     * Tanpa ini input "%../_../\" dari user berperan sebagai wildcard bebas
     * dan backslash dapat merusak pola pencarian. Helper ini wajib ada —
     * fungsi yang hilang akan menyebabkan fatal error 500 di semua
     * halaman list dengan parameter cari.
     */
    function safe_like(string $value): string
    {
        return addcslashes($value, "%_\\");
    }
}

if (! function_exists('sanitize_csv_field')) {
    /**
     * Sanitasi sel CSV (BS-031): cegah formula injection (OWASP WSTG).
     * Sel berawalan =, +, -, @ dieksekusi sebagai rumus di Excel/LibreOffice —
     * diberi prefix petik tunggal. Line-break dibuang agar baris CSV utuh.
     */
    function sanitize_csv_field(?string $value): string
    {
        $value = (string) $value;
        $value = str_replace(["\r", "\n"], ' ', $value);

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            return "'" . $value;
        }

        return $value;
    }
}

if (! function_exists('tipe_transaksi_label')) {
    /**
     * Label tipe transaksi
     */
    function tipe_transaksi_label($tipe): string
    {
        return match ($tipe) {
            'setor' => '<span class="label label-success">SETOR</span>',
            'tarik' => '<span class="label label-danger">TARIK</span>',
            default => '<span class="label label-default">' . strtoupper($tipe) . '</span>',
        };
    }
}

if (! function_exists('status_aktif_label')) {
    /**
     * Label status aktif
     */
    function status_aktif_label($aktif): string
    {
        return $aktif
            ? '<span class="label label-success">Aktif</span>'
            : '<span class="label label-danger">Nonaktif</span>';
    }
}
