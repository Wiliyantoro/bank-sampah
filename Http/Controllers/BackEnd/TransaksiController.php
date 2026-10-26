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

use Modules\BankSampah\Enums\TipeTransaksiEnum;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Modules\BankSampah\Models\LokasiModel;
use Modules\BankSampah\Models\MemberModel;
use Modules\BankSampah\Models\RewardModel;
use Modules\BankSampah\Models\SampahModel;
use Modules\BankSampah\Models\TransaksiDetailModel;
use Modules\BankSampah\Models\TransaksiModel;

class TransaksiController extends AdminModulController
{
    public $moduleName    = 'BankSampah';
    public $modul_ini     = 'bank-sampah';
    public $sub_modul_ini = 'bank-sampah-transaksi';
    public $kategori_pengaturan = 'Bank Sampah';
    public $aliasController   = 'bank-sampah.transaksi';

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    /**
     * Daftar transaksi (filtered per desa)
     */
    public function index()
    {
        if (request()->ajax()) {
            return datatables()->of(TransaksiModel::desa()->with(['member', 'lokasi']))
                ->addIndexColumn()
                ->addColumn('aksi', static function ($row): string {
                    $aksi = '';
                    $aksi .= '<a href="' . ci_route('bank-sampah.transaksi.detail', $row->id) . '" class="btn btn-default btn-sm" title="Detail"><i class="fa fa-eye"></i></a> ';
                    $aksi .= '<a href="' . ci_route('bank-sampah.transaksi.struk', $row->id) . '" class="btn btn-default btn-sm" title="Cetak Struk"><i class="fa fa-print"></i></a>';
                    return $aksi;
                })
                ->rawColumns(['aksi'])
                ->make();
        }

        return view('banksampah::backend.transaksi.index');
    }

    /**
     * Form setor sampah
     */
    public function formSetor()
    {
        $memberList = MemberModel::desa()->aktif()->orderBy('nama')->get();
        $sampahList = SampahModel::desa()->aktif()->with('kategori')->orderBy('kategori_id')->orderBy('nama')->get();
        $lokasiList = LokasiModel::desa()->aktif()->orderBy('nama')->get();

        $selectedMember = null;
        if (request('member_id')) {
            $selectedMember = MemberModel::desa()->find(request('member_id'));
        }

        return view('banksampah::backend.transaksi.form_setor', compact(
            'memberList', 'sampahList', 'lokasiList', 'selectedMember'
        ));
    }

    /**
     * Proses transaksi setor
     */
    public function prosesSetor()
    {
        request()->validate([
            'member_id'        => 'required|integer',
            // BS-016: lokasi/TPS wajib milik desa yang sedang login
            'lokasi_id'        => ['nullable', 'integer', Rule::exists('bank_sampah_lokasi', 'id')
                ->where('config_id', identitas('id'))],
            'item'             => 'required|array|min:1',
            'item.*.sampah_id' => 'required|integer',
            'item.*.berat'     => 'required|numeric|min:0.01',
        ]);

        try {
            // issue #61: DB::transaction(fn) — rollback otomatis, tidak ada
            // jalur return yang bisa lupa rollBack.
            [$member, $total, $totalBerat, $poinDidapat, $transaksi] = DB::transaction(function () {
                $member = MemberModel::desa()->findOrFail(request('member_id'));

                $total = 0;
                $details = [];

                // issue #63: prefetch semua sampah sekali (keyBy), bukan
                // findOrFail per item — N+1 hilang.
                $ids = array_unique(array_column(request('item'), 'sampah_id'));
                $sampahMap = SampahModel::desa()
                    ->whereIn('id', $ids)
                    ->get()
                    ->keyBy('id');

                foreach (request('item') as $item) {
                    $sampah = $sampahMap->get($item['sampah_id'])
                        ?? throw new Exception('jenis sampah tidak ditemukan');

                    $subtotal = $item['berat'] * $sampah->harga_beli;
                    $total += $subtotal;

                    $details[] = [
                        'sampah_id' => $item['sampah_id'],
                        'berat'     => $item['berat'],
                        'harga'     => $sampah->harga_beli,
                        'subtotal'  => $subtotal,
                    ];
                }

                $totalBerat = array_sum(array_column(request('item'), 'berat'));
                $poinPerKg = (int) (setting('bank_sampah_poin_per_kg') ?: 10);
                $poinDidapat = (int) ($totalBerat * $poinPerKg);

                $transaksi = TransaksiModel::create([
                    'no_transaksi' => TransaksiModel::generateNoTransaksi(),
                    'member_id'    => $member->id,
                    'lokasi_id'    => request('lokasi_id') ?: null,
                    'tipe'         => TipeTransaksiEnum::SETOR->value,
                    'total'        => $total,
                    'bayar'        => $total,
                    'poin'         => $poinDidapat,
                    'keterangan'   => request('keterangan'),
                    'user_id'      => auth()->id(),
                ]);

                // issue #63: batch insert detail + update stok dari map
                // yang sudah di-prefetch (tanpa query tambahan per item).
                $now = now();
                DB::table('bank_sampah_transaksi_detail')->insert(array_map(
                    static function (array $detail) use ($transaksi, $now): array {
                        return $detail + [
                            'transaksi_id' => $transaksi->id,
                            'config_id'    => identitas('id'),
                            'created_at'   => $now,
                            'updated_at'   => $now,
                        ];
                    },
                    $details
                ));

                foreach ($details as $detail) {
                    $sampahMap->get($detail['sampah_id'])->tambahStok($detail['berat']);
                }

                $member->tambahSaldo($total);

                if (setting('bank_sampah_reward_aktif') == 1 && $poinDidapat > 0) {
                    $member->tambahPoin($poinDidapat);

                    RewardModel::create([
                        'member_id'    => $member->id,
                        'transaksi_id' => $transaksi->id,
                        'tipe'         => 'add',
                        'poin'         => $poinDidapat,
                        'keterangan'   => 'Poin dari transaksi setor #' . $transaksi->no_transaksi,
                    ]);
                }


                return [$member, $total, $totalBerat, $poinDidapat, $transaksi];
            });
        } catch (Exception $e) {
            // BS-014: pesan exception bisa bocorkan SQL/tabel/path — log penuh, UI generik
            Log::error('[BankSampah] prosesSetor gagal', ['exception' => $e]);
            return redirect_with('error', 'Gagal memproses transaksi. Silakan coba lagi.', ci_route('bank-sampah.transaksi'));
        }

        $this->flushCacheDashboard();

        // issue #56: notifikasi via mekanisme inti (in-app Layanan Mandiri),
        // SETELAH commit dan LUAR try transaksi.
        $this->notifWarga($member, 'Setor tunai berhasil', 'Setor '
            . number_format($totalBerat, 2, ',', '.') . ' kg diterima. Saldo: Rp '
            . number_format($total, 0, ',', '.') . ', poin +' . $poinDidapat . '.');

        return redirect_with('success', 'Transaksi setor berhasil! Total: Rp ' . number_format($total, 0, ',', '.'), ci_route('bank-sampah.transaksi'));
    }

    /**
     * Form tarik saldo
     */
    public function formTarik()
    {
        $memberList = MemberModel::desa()->aktif()->orderBy('nama')->get();

        $selectedMember = null;
        if (request('member_id')) {
            $selectedMember = MemberModel::desa()->find(request('member_id'));
        }

        return view('banksampah::backend.transaksi.form_tarik', compact('memberList', 'selectedMember'));
    }

    /**
     * Proses transaksi tarik
     */
    public function prosesTarik()
    {
        request()->validate([
            'member_id' => 'required|integer',
            'nominal'   => 'required|numeric|min:1',
        ]);

        try {
            // issue #61: DB::transaction(fn) — rollback otomatis.
            [$member, $transaksi] = DB::transaction(function () {
                $member = MemberModel::desa()->findOrFail(request('member_id'));

                if ($member->saldo < request('nominal')) {
                    throw new Exception('saldo_tidak_cukup');
                }

                $transaksi = TransaksiModel::create([
                    'no_transaksi' => TransaksiModel::generateNoTransaksi(),
                    'member_id'    => $member->id,
                    'tipe'         => TipeTransaksiEnum::TARIK->value,
                    'total'        => request('nominal'),
                    'bayar'        => request('nominal'),
                    'keterangan'   => request('keterangan'),
                    'user_id'      => auth()->id(),
                ]);

                if (! $member->kurangiSaldo(request('nominal'))) {
                    throw new Exception('saldo_tidak_cukup');
                }


                return [$member, $transaksi];
            });
        } catch (Exception $e) {
            // BS-014: pesan exception bisa bocorkan SQL/tabel/path — log penuh, UI generik
            Log::error('[BankSampah] prosesTarik gagal', ['exception' => $e]);

            if ($e->getMessage() === 'saldo_tidak_cukup') {
                return redirect_with('error', 'Saldo tidak mencukupi.', ci_route('bank-sampah.transaksi'));
            }

            return redirect_with('error', 'Gagal memproses transaksi. Silakan coba lagi.', ci_route('bank-sampah.transaksi'));
        }

        $this->flushCacheDashboard();

        // issue #56: notifikasi in-app Layanan Mandiri, luar transaksi utama.
        $this->notifWarga($member, 'Tarik tunai berhasil', 'Tarik Rp '
            . number_format(request('nominal'), 0, ',', '.') . ' diproses. Saldo tersisa: Rp '
            . number_format($member->fresh()->saldo, 0, ',', '.') . '.');

        return redirect_with('success', 'Transaksi tarik berhasil! Nominal: Rp ' . number_format(request('nominal'), 0, ',', '.'), ci_route('bank-sampah.transaksi'));
    }

    /**
     * Detail transaksi
     */
    public function detail($id)
    {
        $transaksi = TransaksiModel::desa()
            ->with(['member.lokasi', 'detail.sampah', 'lokasi'])
            ->findOrFail($id);

        return view('banksampah::backend.transaksi.detail', compact('transaksi'));
    }

    /**
     * Cetak struk transaksi
     */
    public function cetakStruk($id)
    {
        $transaksi = TransaksiModel::desa()
            ->with(['member.lokasi', 'detail.sampah', 'lokasi'])
            ->findOrFail($id);

        $formatStruk = setting('bank_sampah_format_struk') ?: 'thermal';

        // Identitas bank sampah untuk struk (Pengaturan Aplikasi, item 5)
        $config = [
            'nama'   => setting('bank_sampah_nama') ?: (identitas('nama_desa') ?: 'Bank Sampah Desa'),
            'alamat' => setting('bank_sampah_alamat') ?: (identitas('alamat_kantor') ?: ''),
            'no_hp'  => setting('bank_sampah_no_hp') ?: (identitas('telepon') ?: ''),
            'logo'   => setting('bank_sampah_logo') ?: '',
        ];

        if ($formatStruk === 'thermal') {
            return view('banksampah::backend.transaksi.struk_thermal', compact('transaksi', 'config'));
        }

        return view('banksampah::backend.transaksi.struk_a4', compact('transaksi', 'config'));
    }

    /**
     * Get data member via AJAX
     */
    /**
     * issue #56: notifikasi in-app ke nasabah lewat log_notifikasi_mandiri
     * (dashboard Layanan Mandiri inti). Gagal tidak mengganggu transaksi.
     * Kontrak FK inti: id_user_mandiri = tweb_penduduk_mandiri.id_pend.
     */
    private function notifWarga(MemberModel $member, string $judul, string $isi): void
    {
        try {
            $mandiri = DB::table('tweb_penduduk_mandiri')
                ->where('id_pend', $member->penduduk_id)
                ->where('config_id', identitas('id'))
                ->value('id_pend');

            if (empty($mandiri)) {
                return;
            }

            DB::table('log_notifikasi_mandiri')->insert([
                'id_user_mandiri' => $mandiri,
                'config_id'       => identitas('id'),
                'judul'           => $judul,
                'isi'             => $isi,
                'payload'         => 'bank-sampah',
                'read'            => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('[BankSampah] Gagal kirim notifikasi in-app warga', [
                'member_id' => $member->id,
                'exception' => $e,
            ]);
        }
    }

    /**
     * issue #64: cache dashboard/grafik basi setelah transaksi — dibuang
     * langsung setelah commit (dulu: 0 Cache::forget, angka basi).
     */
    private function flushCacheDashboard(): void
    {
        $configId = identitas('id');

        Cache::forget("banksampah_dash_{$configId}_" . date('Ym'));
        Cache::forget("banksampah_dash_grafik_{$configId}");
    }

    /**
     * issue #64: AJAX cari nasabah untuk select2 (dropdown transaksi
     * tidak lagi menarik semua member aktif saat render form).
     */
    public function cariMember()
    {
        $query = MemberModel::desa()->aktif()->orderBy('nama')->limit(20);

        // issue #66: when() menggantikan if manual agar query builder tetap
        // satu rantai; filled() dipakai sebagai semantik "param dikirim isi"
        $query->when(request()->filled('q'), function ($q) {
            $safeCari = bank_sampah_safe_like(trim((string) request('q')));
            $q->where(function ($w) use ($safeCari) {
                $w->where('nama', 'LIKE', "%{$safeCari}%")
                  ->orWhere('no_member', 'LIKE', "%{$safeCari}%");
            });
        });

        return response()->json([
            'results' => $query->get()
                ->map(static fn ($m) => [
                    'id'   => $m->id,
                    'text' => $m->no_member . ' - ' . $m->nama . ' (Rp ' . number_format($m->saldo, 0, ',', '.') . ')',
                ]),
        ]);
    }

    public function getMember()
    {
        $member = MemberModel::desa()->findOrFail(request('id'));

        return response()->json([
            'id'        => $member->id,
            'no_member' => $member->no_member,
            'nama'      => $member->nama,
            'saldo'     => $member->saldo,
            'poin'      => $member->poin,
            'no_hp'     => $member->no_hp,
        ]);
    }

    /**
     * Get harga sampah via AJAX
     */
    public function getSampah()
    {
        $sampah = SampahModel::desa()->with('kategori')->findOrFail(request('id'));

        return response()->json([
            'id'         => $sampah->id,
            'nama'       => $sampah->nama,
            'satuan'     => $sampah->satuan,
            'harga_beli' => $sampah->harga_beli,
            'kategori'   => $sampah->kategori->nama,
        ]);
    }
}
