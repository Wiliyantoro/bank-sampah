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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\BankSampah\Libraries\SecurityManager;
use Modules\BankSampah\Models\BankSampahMember;
use Modules\BankSampah\Models\BankSampahTransaksi;
use Modules\BankSampah\Models\BankSampahWargaSetting;

class BankSampahWargaController extends Controller
{
    protected SecurityManager $security;

    public function __construct(SecurityManager $security)
    {
        $this->security = $security;
    }

    /**
     * Dashboard warga (public access via session-based auth)
     */
    public function index(Request $request)
    {
        $member = null;
        $riwayat = collect();
        $settings = null;

        $configId = identitas('id');
        $settings = Cache::remember(BankSampahWargaSetting::cacheKey($configId), BankSampahWargaSetting::CACHE_TTL, function () use ($configId) {
            return BankSampahWargaSetting::getSettings($configId);
        });

        if (! $settings->warga_aktif) {
            return view('banksampah::backend.warga.index', [
                'member'   => null,
                'riwayat'  => collect(),
                'settings' => $settings,
                'error'    => 'Dashboard nasabah sedang tidak aktif.',
            ]);
        }

        $wargaMemberId = $request->session()->get('warga_member_id');

        if ($wargaMemberId) {
            // Defense-in-depth: filter by config_id juga
            $member = BankSampahMember::desa()
                ->where('id', $wargaMemberId)
                ->where('aktif', 1)
                ->first();

            if (! $member) {
                $request->session()->forget('warga_member_id');
                $request->session()->forget('warga_authenticated');

                return view('banksampah::backend.warga.index', [
                    'member'   => null,
                    'riwayat'  => collect(),
                    'settings' => $settings,
                    'error'    => 'Sesi expired. Silakan login kembali.',
                ]);
            }

            // Verifikasi PIN
            if ($settings->require_pin) {
                // (BS-003) Fail-closed: member tanpa PIN TIDAK dapat akses apa pun.
                // Aktivasi PIN hanya via admin (member/set-pin/{id}) — self-service
                // tanpa PIN lama justru membuka pembajakan akun bagi login satu-faktor.
                if (empty($member->pin)) {
                    $request->session()->forget('warga_member_id');
                    $request->session()->forget('warga_authenticated');
                    $this->security->logEvent('warga_pin_missing', ['member_id' => $member->id], 'warning');

                    return view('banksampah::backend.warga.index', [
                        'member'   => null,
                        'riwayat'  => collect(),
                        'settings' => $settings,
                        'error'    => 'Akun Anda belum memiliki PIN untuk keamanan data. Silakan hubungi pengurus Bank Sampah untuk aktivasi PIN, atau aktivasi di loket.',
                    ]);
                }

                $isPinVerified = $request->session()->get('warga_authenticated', false);

                if (! $isPinVerified) {
                    $pin = $request->input('pin', '');

                    if (empty($pin)) {
                        return view('banksampah::backend.warga.index', [
                            'member'   => null,
                            'riwayat'  => collect(),
                            'settings' => $settings,
                            'showPin'  => true,
                            'error'    => null,
                        ]);
                    }

                    if (! $this->security->validatePinFormat($pin)) {
                        $this->security->logEvent('pin_invalid_format', [
                            'member_id' => $member->id,
                            'ip'        => $request->ip(),
                        ], 'warning');

                        return view('banksampah::backend.warga.index', [
                            'member'   => null,
                            'riwayat'  => collect(),
                            'settings' => $settings,
                            'showPin'  => true,
                            'error'    => 'PIN tidak valid.',
                        ]);
                    }

                    $pinRateLimit = $this->security->checkPinRateLimit($member->id);

                    if ($pinRateLimit['blocked']) {
                        $minutes = ceil($pinRateLimit['retry_after'] / 60);
                        $this->security->logEvent('pin_lockout', ['member_id' => $member->id], 'warning');

                        return view('banksampah::backend.warga.index', [
                            'member'   => null,
                            'riwayat'  => collect(),
                            'settings' => $settings,
                            'showPin'  => false,
                            'error'    => "Akun terkunci. Terlalu banyak percobaan PIN. Tunggu {$minutes} menit.",
                        ]);
                    }

                    if (! $this->security->verifyPin($pin, $member->pin)) {
                        $this->security->recordPinAttempt($member->id);
                        $this->security->logPinVerification($member->id, false);

                        $remaining = $pinRateLimit['remaining'] - 1;

                        return view('banksampah::backend.warga.index', [
                            'member'   => null,
                            'riwayat'  => collect(),
                            'settings' => $settings,
                            'showPin'  => true,
                            'error'    => "PIN tidak valid. Sisa percobaan: {$remaining}",
                        ]);
                    }

                    $this->security->resetPinRateLimit($member->id);
                    $this->security->logPinVerification($member->id, true);
                    $request->session()->put('warga_authenticated', true);
                }
            }

            // Ambil riwayat transaksi
            if ($settings->show_riwayat) {
                $riwayat = BankSampahTransaksi::desa()
                    ->where('member_id', $member->id)
                    ->orderBy('created_at', 'desc')
                    ->limit($settings->max_riwayat)
                    ->get();
            }
        }

        return view('banksampah::backend.warga.index', compact('member', 'riwayat', 'settings'));
    }

    /**
     * Login warga via no_member + no_hp
     */
    public function login(Request $request)
    {
        $ip = $request->ip();
        $rateLimit = $this->security->checkLoginRateLimit($ip);

        if ($rateLimit['blocked']) {
            $minutes = ceil($rateLimit['retry_after'] / 60);
            $this->security->logLoginAttempt('unknown', false, 'rate_limited');

            return redirect(url('bank-sampah/warga'))
                ->with('error', "Terlalu banyak percobaan login. Tunggu {$minutes} menit.");
        }

        // BS-012: format check beneran di validasi Laravel — detectSuspiciousInput
        // tidak lagi dipakai untuk memblok (false positive tinggi pada input sah)
        $request->validate([
            'no_member' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9\-]+$/'],
            'no_hp'     => ['required', 'string', 'max:20', 'regex:/^[0-9+()\- ]+$/'],
        ]);

        $noMember = $this->security->sanitize($request->no_member);
        $noHp = $this->security->sanitize($request->no_hp);

        // Rate limit per IP + per no_member (aksi #3: tahan brute-force via rotasi IP)
        $rateLimit = $this->security->checkLoginRateLimit($ip, $noMember);

        if ($rateLimit['blocked']) {
            $minutes = ceil($rateLimit['retry_after'] / 60);
            $this->security->logLoginAttempt($noMember, false, 'rate_limited');

            return redirect(url('bank-sampah/warga'))
                ->with('error', "Terlalu banyak percobaan login. Tunggu {$minutes} menit.");
        }

        // Generic error message to prevent user enumeration
        $genericError = 'Nomor member atau nomor HP tidak valid';

        $member = BankSampahMember::desa()
            ->where('no_member', $noMember)
            ->where('aktif', 1)
            ->first();

        if (! $member) {
            $this->security->recordLoginAttempt($ip, $noMember);
            $this->security->logLoginAttempt($noMember, false, 'member_not_found');

            return redirect(url('bank-sampah/warga'))
                ->with('error', $genericError);
        }

        if ($member->no_hp !== $noHp) {
            $this->security->recordLoginAttempt($ip, $noMember);
            $this->security->logLoginAttempt($noMember, false, 'invalid_phone');

            return redirect(url('bank-sampah/warga'))
                ->with('error', $genericError);
        }

        // Login berhasil
        $this->security->resetRateLimit('login_' . $ip);
        $this->security->resetRateLimit('login_member_' . md5($noMember));
        $this->security->logLoginAttempt($noMember, true);

        // Regenerate session to prevent session fixation
        $request->session()->regenerate();
        $request->session()->put('warga_member_id', $member->id);
        $request->session()->put('warga_authenticated', false);

        $this->security->logEvent('warga_login_success', [
            'member_id' => $member->id,
        ]);

        return redirect(url('bank-sampah/warga'));
    }

    /**
     * Generate link share untuk nasabah (admin only)
     */
    public function shareLink($memberId)
    {
        if (! auth()->check()) {
            abort(403, 'Unauthorized');
        }

        $member = BankSampahMember::desa()->findOrFail($memberId);

        if (empty($member->uuid)) {
            $member->uuid = $this->security->generateToken();
            $member->save();
        }

        $shareToken = $this->security->createExpiringToken($member->id, 60);
        $shareUrl = url('bank-sampah/warga/share/' . $shareToken);

        $this->security->logEvent('share_link_generated', ['member_id' => $memberId]);

        return response()->json([
            'success' => true,
            'data'    => [
                'uuid'       => $member->uuid,
                'share_url'  => $shareUrl,
                'expires_in' => '1 jam',
            ],
        ]);
    }

    /**
     * Share link access — validates expiring token, auto-login via session
     */
    public function shareAccess(Request $request, $token)
    {
        $configId = identitas('id');
        $settings = Cache::remember(BankSampahWargaSetting::cacheKey($configId), BankSampahWargaSetting::CACHE_TTL, function () use ($configId) {
            return BankSampahWargaSetting::getSettings($configId);
        });

        if (! $settings->warga_aktif) {
            return view('banksampah::backend.warga.index', [
                'member'   => null,
                'riwayat'  => collect(),
                'settings' => $settings,
                'error'    => 'Dashboard nasabah sedang tidak aktif.',
            ]);
        }

        $memberId = $this->security->validateExpiringToken($token);

        if (! $memberId) {
            $this->security->logEvent('share_link_expired', ['token' => substr($token, 0, 8) . '...'], 'warning');

            return view('banksampah::backend.warga.index', [
                'member'   => null,
                'riwayat'  => collect(),
                'settings' => $settings,
                'error'    => 'Link sudah expired atau tidak valid. Silakan minta link baru.',
            ]);
        }

        $member = BankSampahMember::desa()
            ->where('id', $memberId)
            ->where('aktif', 1)
            ->first();

        if (! $member) {
            return view('banksampah::backend.warga.index', [
                'member'   => null,
                'riwayat'  => collect(),
                'settings' => $settings,
                'error'    => 'Nasabah tidak ditemukan.',
            ]);
        }

        // Token valid — auto-login via session
        $request->session()->regenerate();
        $request->session()->put('warga_member_id', $member->id);
        $request->session()->put('warga_authenticated', false);

        $this->security->logEvent('share_link_login', ['member_id' => $member->id]);

        return redirect(url('bank-sampah/warga'));
    }

    /**
     * Reset UUID (regenerate) — admin only
     */
    public function resetUuid($memberId)
    {
        if (! auth()->check()) {
            abort(403, 'Unauthorized');
        }

        $member = BankSampahMember::desa()->findOrFail($memberId);
        $oldUuid = $member->uuid;
        $member->uuid = $this->security->generateToken();
        $member->save();

        $this->security->logEvent('uuid_reset', [
            'member_id' => $memberId,
            'old_uuid'  => substr($oldUuid, 0, 8) . '...',
            'admin_id'  => auth()->id(),
        ]);

        return redirect()->back()
            ->with('success', 'UUID berhasil di-reset. Link lama tidak berlaku lagi.');
    }

    /**
     * Set PIN untuk nasabah — admin only
     */
    public function setPin(Request $request, $memberId)
    {
        if (! auth()->check()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'pin' => 'required|string',
        ]);

        $pin = $request->pin;

        if (! $this->security->validatePinFormat($pin)) {
            return redirect()->back()
                ->with('error', 'PIN harus 4-6 digit angka.');
        }

        if ($this->security->isCommonPin($pin)) {
            return redirect()->back()
                ->with('error', 'PIN terlalu umum. Gunakan kombinasi angka yang lebih unik.');
        }

        $member = BankSampahMember::desa()->findOrFail($memberId);
        $member->pin = $this->security->hashPin($pin);
        $member->save();

        $this->security->logEvent('pin_changed', [
            'member_id' => $memberId,
            'admin_id'  => auth()->id(),
        ]);

        return redirect()->back()
            ->with('success', 'PIN berhasil diatur.');
    }

    /**
     * Logout warga — clear session + invalidate
     */
    public function logout(Request $request)
    {
        $request->session()->forget('warga_member_id');
        $request->session()->forget('warga_authenticated');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(url('bank-sampah/warga'));
    }
}
