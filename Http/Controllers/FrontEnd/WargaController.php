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

use Modules\BankSampah\Libraries\SecurityManager;
use Modules\BankSampah\Models\MemberModel;
use Modules\BankSampah\Models\TransaksiModel;

/**
 * Dashboard warga — FRONTEND (publik).
 *
 * D-001 (keputusan desain): autentikasi warga memakai sesi Layanan Mandiri
 * inti (NIK + PIN di tweb_penduduk). TIDAK ada login kedua, kolom `pin`
 * member, rate-limit PIN, atau tabel bank_sampah_warga_settings di modul ini.
 * Sesi modul `bank_sampah_member_id` hanya dipakai untuk akses via share-link.
 */
class WargaController extends WebModulController
{
    public $moduleName = 'BankSampah';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Dashboard warga
     */
    public function index()
    {
        $member  = null;
        $riwayat = collect();

        if (setting('bank_sampah_warga_aktif') != 1) {
            return view('banksampah::backend.warga.index', [
                'member'   => null,
                'riwayat'  => collect(),
                'error'    => 'Dashboard nasabah sedang tidak aktif.',
            ]);
        }

        $member = $this->memberSaatIni();

        if ($member && setting('bank_sampah_show_riwayat') == 1) {
            $riwayat = TransaksiModel::desa()
                ->where('member_id', $member->id)
                ->orderBy('created_at', 'desc')
                ->limit((int) (setting('bank_sampah_max_riwayat') ?: 20))
                ->get();
        }

        return view('banksampah::backend.warga.index', [
            'member'        => $member,
            'riwayat'       => $riwayat,
            'mandiriLogin'  => site_url('first/mandiri'),
            'error'         => null,
        ]);
    }

    /**
     * Tentukan member aktif: sesi modul (share-link) > sesi Layanan Mandiri inti.
     * Defense-in-depth: selalu difilter config_id + aktif.
     */
    protected function memberSaatIni()
    {
        $memberId = session('bank_sampah_member_id');

        if ($memberId) {
            $member = MemberModel::desa()->where('id', $memberId)->where('aktif', 1)->first();

            if (! $member) {
                session()->forget('bank_sampah_member_id');
            }

            return $member;
        }

        // D-001: warga sudah login di Layanan Mandiri (sesi inti berisi
        // id penduduk) → cocokkan member via penduduk_id
        $pendudukId = session('mandiri');

        if ($pendudukId) {
            return MemberModel::desa()
                ->where('penduduk_id', $pendudukId)
                ->where('aktif', 1)
                ->first();
        }

        return null;
    }

    /**
     * Share link access — validasi token expiring, set sesi modul
     */
    public function shareAccess($token)
    {
        if (setting('bank_sampah_warga_aktif') != 1) {
            return view('banksampah::backend.warga.index', [
                'member'   => null,
                'riwayat'  => collect(),
                'error'    => 'Dashboard nasabah sedang tidak aktif.',
            ]);
        }

        $security = app(SecurityManager::class);
        $memberId = $security->validateExpiringToken($token);

        if (! $memberId) {
            $security->logEvent('share_link_expired', ['token' => substr($token, 0, 8) . '...'], 'warning');

            return view('banksampah::backend.warga.index', [
                'member'   => null,
                'riwayat'  => collect(),
                'error'    => 'Link sudah expired atau tidak valid. Silakan minta link baru.',
            ]);
        }

        $member = MemberModel::desa()->where('id', $memberId)->where('aktif', 1)->first();

        if (! $member) {
            return view('banksampah::backend.warga.index', [
                'member'   => null,
                'riwayat'  => collect(),
                'error'    => 'Nasabah tidak ditemukan.',
            ]);
        }

        // Token valid — set sesi modul (BS-040: regenerasi id sesi)
        session()->regenerate();
        session()->put('bank_sampah_member_id', $member->id);

        $security->logEvent('share_link_login', ['member_id' => $member->id]);

        return redirect(url('bank-sampah/warga'));
    }

    /**
     * Logout warga — hanya hapus sesi milik modul.
     * Sesi Layanan Mandiri inti TIDAK disentuh.
     */
    public function logout()
    {
        session()->forget('bank_sampah_member_id');

        return redirect(url('bank-sampah/warga'));
    }
}
