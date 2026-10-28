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

use Modules\BankSampah\Models\MemberModel;
use Modules\BankSampah\Models\TransaksiModel;
use Modules\BankSampah\Services\IdentitasBankSampah;

class MemberInfoController extends AdminModulController
{
    public $moduleName    = 'BankSampah';
    public $modul_ini     = 'bank-sampah';
    public $sub_modul_ini = 'bank-sampah-member';
    public $kategori_pengaturan = 'Bank Sampah';
    public $aliasController   = 'bank-sampah.member';

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    /**
     * Detail nasabah + riwayat transaksi
     */
    public function detail($id)
    {
        // issue #63: eager load transaksi.detail.sampah tanpa batas dihapus —
        // riwayat sudah disajikan via $riwayatTransaksi yang di-paginate.
        $member = MemberModel::desa()->with('lokasi')
            ->findOrFail($id);

        $riwayatTransaksi = TransaksiModel::query()
            ->where('member_id', $id)
            ->with('detail.sampah')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('banksampah::backend.member.detail', compact('member', 'riwayatTransaksi'));
    }

    /**
     * Cetak kartu nasabah (QR Code)
     */
    public function cetakKartu($id)
    {
        $member = MemberModel::query()->with('lokasi')->findOrFail($id);

        // Identitas bank sampah dari Pengaturan Aplikasi (item 5)
        $config = app(IdentitasBankSampah::class)->data();

        // BS-023: QR memuat URL dashboard (halaman login) — bukan data member.
        // no_member = separuh kredensial login warga; tidak boleh dikodekan di QR.
        $shareUrl = url('bank-sampah/warga');

        return view('banksampah::backend.member.kartu', compact('member', 'config', 'shareUrl'));
    }

}
