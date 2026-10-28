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
     * Set PIN Layanan Mandiri warga — PIN jadi satu dengan Layanan Mandiri
     * inti (D-001): disimpan di `tweb_penduduk_mandiri.pin` (bcrypt),
     * satu row per penduduk per desa.
     *
     * #100: sebelumnya menulis `tweb_penduduk.pin` — kolom ini TIDAK ADA
     * di inti v2610.1.0 → pasti 500 (errno 1054).
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

        $hash = password_hash($pin, PASSWORD_BCRYPT);
        $configId = identitas('id');

        $mandiri = DB::table('tweb_penduduk_mandiri')
            // BS-015/BS-036: hanya penduduk desa aktif yang bersangkutan
            ->where('config_id', $configId)
            ->where('id_pend', $member->penduduk_id)
            ->first();

        if ($mandiri) {
            // Akun mandiri sudah ada → ganti PIN-nya saja
            DB::table('tweb_penduduk_mandiri')
                ->where('id', $mandiri->id)
                ->update(['pin' => $hash, 'updated_at' => now()]);
        } else {
            // Belum punya akun mandiri → buatkan, langsung aktif
            DB::table('tweb_penduduk_mandiri')->insert([
                'config_id'    => $configId,
                'id_pend'      => $member->penduduk_id,
                'pin'          => $hash,
                'aktif'        => 1,
                'ganti_pin'    => 0,
                'tanggal_buat' => now(),
                'updated_at'   => now(),
            ]);
        }

        $security->logEvent('pin_changed', [
            'member_id' => $id,
            'admin_id'  => auth()->id(),
        ]);

        return redirect_with('success', 'PIN Layanan Mandiri berhasil diatur. Warga dapat login dengan NIK + PIN ini.', ci_route('bank-sampah.member'));
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
