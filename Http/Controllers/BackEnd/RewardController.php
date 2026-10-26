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
use Illuminate\Support\Facades\Log;
use Modules\BankSampah\Models\MemberModel;
use Modules\BankSampah\Models\RewardModel;

class RewardController extends AdminModulController
{
    public $moduleName    = 'BankSampah';
    public $modul_ini     = 'bank-sampah';
    public $sub_modul_ini = 'bank-sampah-reward';
    public $kategori_pengaturan = 'Bank Sampah';
    public $aliasController   = 'bank-sampah.reward';

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    /**
     * Dashboard reward & poin (filtered per desa)
     */
    public function index()
    {
        if (request()->ajax()) {
            return datatables()->of(MemberModel::query()->aktif()->orderBy('poin', 'desc'))
                ->addIndexColumn()
                ->addColumn('aksi', static function ($row): string {
                    $aksi = '<a href="' . ci_route('bank-sampah.reward.riwayat', $row->id) . '" class="btn btn-default btn-sm" title="Riwayat Poin"><i class="fa fa-history"></i></a> ';
                    if (can('u')) {
                        $aksi .= '<a href="' . ci_route('bank-sampah.reward.tukar', $row->id) . '" class="btn btn-info btn-sm" title="Tukar Poin"><i class="fa fa-exchange"></i></a>';
                    }
                    return $aksi;
                })
                ->rawColumns(['aksi'])
                ->make();
        }

        $totalPoin = MemberModel::query()->aktif()->sum('poin');

        $riwayatReward = RewardModel::query()->with('member')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('banksampah::backend.reward.index', compact('totalPoin', 'riwayatReward'));
    }

    /**
     * Form tukar poin
     */
    public function formTukar($memberId)
    {
        $member = MemberModel::query()->findOrFail($memberId);
        $config = [
            'reward' => [
                'enabled'            => setting('bank_sampah_reward_aktif') == 1,
                'minimal_poin_tukar' => (int) (setting('bank_sampah_minimal_poin_tukar') ?: 100),
            ],
        ];

        return view('banksampah::backend.reward.form_tukar', compact('member', 'config'));
    }

    /**
     * Proses tukar poin
     */
    public function prosesTukar($memberId)
    {
        request()->validate([
            'poin'       => 'required|integer|min:1',
            'keterangan' => 'required|string|max:255',
        ]);

        $member  = MemberModel::query()->findOrFail($memberId);
        $minimal = (int) (setting('bank_sampah_minimal_poin_tukar') ?: 100);

        if ($member->poin < request('poin')) {
            return redirect_with('error', 'Poin tidak mencukupi. Poin saat ini: ' . $member->poin);
        }

        if (request('poin') < $minimal) {
            return redirect_with('error', "Minimal poin untuk tukar adalah {$minimal}", ci_route('bank-sampah.reward'));
        }

        try {
            // issue #61: DB::transaction(fn) — rollback otomatis.
            DB::transaction(function () use ($member) {
                if (! $member->tukarPoin(request('poin'))) {
                    throw new Exception('poin_tidak_cukup');
                }

                RewardModel::create([
                    'member_id'   => $member->id,
                    'tipe'        => 'tukar',
                    'poin'        => request('poin'),
                    'keterangan'  => request('keterangan'),
                ]);
            });

            return redirect_with('success', 'Berhasil menukar ' . request('poin') . ' poin untuk ' . $member->nama);
        } catch (Exception $e) {
            // BS-014: pesan exception bisa bocorkan SQL/tabel/path — log penuh, UI generik
            Log::error('[BankSampah] prosesTukar gagal', ['exception' => $e]);

            if ($e->getMessage() === 'poin_tidak_cukup') {
                return redirect_with('error', 'Poin tidak mencukupi. Silakan coba lagi.', ci_route('bank-sampah.reward'));
            }
            return redirect_with('error', 'Gagal menukar poin. Silakan coba lagi.', ci_route('bank-sampah.reward'));
        }
    }

    /**
     * Riwayat poin per nasabah
     */
    public function riwayat($memberId)
    {
        if (request()->ajax()) {
            return datatables()->of(RewardModel::query()->where('member_id', $memberId))
                ->addIndexColumn()
                ->make();
        }

        $member = MemberModel::query()->findOrFail($memberId);

        return view('banksampah::backend.reward.riwayat', compact('member'));
    }
}
