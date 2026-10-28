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

use Illuminate\Support\Facades\DB;
use Modules\BankSampah\Http\Requests\SetPinRequest;
use Modules\BankSampah\Services\SecurityManager;
use Modules\BankSampah\Models\MemberModel;

class MemberAkunController extends AdminModulController
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

    // ==================== Aksi admin atas akun warga (D-001) ====================
    // Dipisah dari MemberController (D-004: anti-bengkak, issue #92).

    // ==================== Aksi admin atas akun warga (D-001) ====================

    /**
     * Reset UUID — link share lama tidak berlaku lagi
     */
    public function resetUuid($id)
    {
        isCan('u');

        $member = MemberModel::query()->findOrFail($id);
        $oldUuid = $member->uuid;
        $member->resetUuid();

        $security = app(SecurityManager::class);
        $security->logEvent('uuid_reset', [
            'member_id' => $id,
            'old_uuid'  => substr($oldUuid, 0, 8) . '...',
            'admin_id'  => auth()->id(),
        ]);

        return redirect_with('success', 'UUID berhasil di-reset. Link lama tidak berlaku lagi.', ci_route('bank-sampah.member'));
    }

    /**
     * Set PIN Layanan Mandiri warga (D-001: PIN milik sesi inti, disimpan
     * pada tweb_penduduk.pin — bukan kolom pin tabel member).
     */
    public function setPin($id)
    {
        isCan('u');

        $req = SetPinRequest::createFrom(request());
        $req->validate($req->rules());

        $member = MemberModel::query()->findOrFail($id);

        if (empty($member->penduduk_id)) {
            return redirect_with('error', 'Nasabah belum terhubung dengan data penduduk. Hubungkan dulu untuk mengatur PIN Layanan Mandiri.', ci_route('bank-sampah.member'));
        }

        $pin = request('pin');
        $security = app(SecurityManager::class);

        if ($security->isCommonPin($pin)) {
            return redirect_with('error', 'PIN terlalu umum. Gunakan kombinasi angka yang lebih unik.', ci_route('bank-sampah.member'));
        }

        DB::table('tweb_penduduk')
            // BS-015/BS-036: hanya penduduk desa aktif
            ->where('config_id', identitas('id'))
            ->where('id', $member->penduduk_id)
            ->update(['pin' => $pin]);

        $security->logEvent('pin_changed', [
            'member_id' => $id,
            'admin_id'  => auth()->id(),
        ]);

        return redirect_with('success', 'PIN berhasil diatur.', ci_route('bank-sampah.member'));
    }

    /**
     * Generate link share untuk nasabah
     */
    public function shareLink($id)
    {
        isCan('u');

        $member = MemberModel::query()->findOrFail($id);

        if (empty($member->uuid)) {
            $member->uuid = app(SecurityManager::class)->generateToken();
            $member->save();
        }

        $security  = app(SecurityManager::class);
        $shareToken = $security->createExpiringToken($member->id, 60);
        $shareUrl   = url('bank-sampah/warga/share/' . $shareToken);

        $security->logEvent('share_link_generated', ['member_id' => $id]);

        return response()->json([
            'success' => true,
            'data'    => [
                'uuid'       => $member->uuid,
                'share_url'  => $shareUrl,
                'expires_in' => '1 jam',
            ],
        ]);
    }

}
