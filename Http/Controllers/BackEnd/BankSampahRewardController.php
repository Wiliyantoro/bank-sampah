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
use Illuminate\Support\Facades\DB;
use Modules\BankSampah\Models\BankSampahMember;
use Modules\BankSampah\Models\BankSampahReward;

class BankSampahRewardController extends Controller
{
    /**
     * Dashboard reward & poin (filtered per desa)
     */
    public function index(Request $request)
    {
        $query = BankSampahMember::desa()->aktif()->orderBy('poin', 'desc');

        if ($request->has('cari') && $request->cari !== '') {
            $cari = $request->cari;
            $safeCari = safe_like($cari);
            $query->where(function ($q) use ($safeCari) {
                $q->where('nama', 'LIKE', "%{$safeCari}%")
                  ->orWhere('no_member', 'LIKE', "%{$safeCari}%");
            });
        }

        if ($request->has('ada_poin') && $request->ada_poin === '1') {
            $query->where('poin', '>', 0);
        }

        $member = $query->paginate(20);
        $totalPoin = BankSampahMember::desa()->aktif()->sum('poin');

        $riwayatReward = BankSampahReward::desa()->with('member')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('banksampah::backend.reward.index', compact('member', 'totalPoin', 'riwayatReward'));
    }

    /**
     * Form tukar poin
     */
    public function formTukar($memberId)
    {
        $member = BankSampahMember::desa()->findOrFail($memberId);
        $config = config('banksampah');

        return view('banksampah::backend.reward.form_tukar', compact('member', 'config'));
    }

    /**
     * Proses tukar poin
     */
    public function prosesTukar(Request $request, $memberId)
    {
        $request->validate([
            'poin'       => 'required|integer|min:1',
            'keterangan' => 'required|string|max:255',
        ]);

        $member = BankSampahMember::desa()->findOrFail($memberId);
        $config = config('banksampah');

        if ($member->poin < $request->poin) {
            return redirect()->back()
                ->with('error', 'Poin tidak mencukupi. Poin saat ini: ' . $member->poin);
        }

        if ($request->poin < $config['reward']['minimal_poin_tukar']) {
            return redirect()->back()
                ->with('error', "Minimal poin untuk tukar adalah {$config['reward']['minimal_poin_tukar']}");
        }

        DB::beginTransaction();

        try {
            $member->tukarPoin($request->poin);

            BankSampahReward::create([
                'config_id'   => identitas('id'),
                'member_id'   => $member->id,
                'tipe'        => 'tukar',
                'poin'        => $request->poin,
                'keterangan'  => $request->keterangan,
            ]);

            DB::commit();

            return redirect()->route('bank-sampah/reward')
                ->with('success', "Berhasil menukar {$request->poin} poin untuk {$member->nama}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menukar poin: ' . $e->getMessage());
        }
    }

    /**
     * Riwayat poin per nasabah
     */
    public function riwayat($memberId)
    {
        $member = BankSampahMember::desa()->findOrFail($memberId);

        $riwayat = BankSampahReward::desa()
            ->where('member_id', $memberId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('banksampah::backend.reward.riwayat', compact('member', 'riwayat'));
    }
}
