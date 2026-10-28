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

/**
 * Identitas bank sampah untuk cetakan/kartu (Pengaturan Aplikasi, item 5):
 * nilai kosong otomatis memakai profil desa.
 *
 * Dikeluarkan dari MemberController (D-004: controller ramping, helper
 * cetak dipakai lintas-konteks cetak).
 */
class IdentitasBankSampah
{
    public function data(): array
    {
        return [
            'nama'   => setting('bank_sampah_nama') ?: (identitas('nama_desa') ?: 'Bank Sampah Desa'),
            'alamat' => setting('bank_sampah_alamat') ?: (identitas('alamat_kantor') ?: ''),
            'no_hp'  => setting('bank_sampah_no_hp') ?: (identitas('telepon') ?: ''),
            'logo'   => setting('bank_sampah_logo') ?: '',
        ];
    }
}
