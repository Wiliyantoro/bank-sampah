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
            return datatables()->of(TransaksiModel::query()->with('member'))
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
        $memberList = MemberModel::query()->aktif()->orderBy('nama')->get();
        $sampahList = SampahModel::query()->aktif()->with('kategori')->orderBy('kategori_id')->orderBy('nama')->get();
        $lokasiList = LokasiModel::query()->aktif()->orderBy('nama')->get();

        $selectedMember = null;
        if (request('member_id')) {
            $selectedMember = MemberModel::query()->find(request('member_id'));
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

        DB::beginTransaction();

        try {
            $member = MemberModel::query()->findOrFail(request('member_id'));

            $total = 0;
            $details = [];

            foreach (request('item') as $item) {
                $sampah = SampahModel::query()->findOrFail($item['sampah_id']);
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
                'tipe'         => 'setor',
                'total'        => $total,
                'bayar'        => $total,
                'poin'         => $poinDidapat,
                'keterangan'   => request('keterangan'),
                'user_id'      => auth()->id(),
            ]);

            foreach ($details as $detail) {
                TransaksiDetailModel::create(array_merge($detail, [
                    'transaksi_id' => $transaksi->id,
                ]));

                $sampah = SampahModel::query()->find($detail['sampah_id']); // BS-040: konsisten scope desa
                $sampah->tambahStok($detail['berat']);
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

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            // BS-014: pesan exception bisa bocorkan SQL/tabel/path — log penuh, UI generik
            Log::error('[BankSampah] prosesSetor gagal', ['exception' => $e]);
            return redirect_with('error', 'Gagal memproses transaksi. Silakan coba lagi.', ci_route('bank-sampah.transaksi'));
        }

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
        $memberList = MemberModel::query()->aktif()->orderBy('nama')->get();

        $selectedMember = null;
        if (request('member_id')) {
            $selectedMember = MemberModel::query()->find(request('member_id'));
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

        DB::beginTransaction();

        try {
            $member = MemberModel::query()->findOrFail(request('member_id'));

            if ($member->saldo < request('nominal')) {
                DB::rollBack();
                return redirect_with('error', 'Saldo tidak mencukupi. Saldo saat ini: Rp ' . number_format($member->saldo, 0, ',', '.'), ci_route('bank-sampah.transaksi'));
            }

            $transaksi = TransaksiModel::create([
                'no_transaksi' => TransaksiModel::generateNoTransaksi(),
                'member_id'    => $member->id,
                'tipe'         => 'tarik',
                'total'        => request('nominal'),
                'bayar'        => request('nominal'),
                'keterangan'   => request('keterangan'),
                'user_id'      => auth()->id(),
            ]);

            if (! $member->kurangiSaldo(request('nominal'))) {
                DB::rollBack();
                return redirect_with('error', 'Saldo tidak mencukupi. Silakan coba lagi.', ci_route('bank-sampah.transaksi'));
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            // BS-014: pesan exception bisa bocorkan SQL/tabel/path — log penuh, UI generik
            Log::error('[BankSampah] prosesTarik gagal', ['exception' => $e]);
            return redirect_with('error', 'Gagal memproses transaksi. Silakan coba lagi.', ci_route('bank-sampah.transaksi'));
        }

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
        $transaksi = TransaksiModel::query()
            ->with(['member.lokasi', 'detail.sampah', 'lokasi'])
            ->findOrFail($id);

        return view('banksampah::backend.transaksi.detail', compact('transaksi'));
    }

    /**
     * Cetak struk transaksi
     */
    public function cetakStruk($id)
    {
        $transaksi = TransaksiModel::query()
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
    public function getMember()
    {
        $member = MemberModel::query()->findOrFail(request('id'));

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
        $sampah = SampahModel::query()->with('kategori')->findOrFail(request('id'));

        return response()->json([
            'id'         => $sampah->id,
            'nama'       => $sampah->nama,
            'satuan'     => $sampah->satuan,
            'harga_beli' => $sampah->harga_beli,
            'kategori'   => $sampah->kategori->nama,
        ]);
    }


    /**
     * issue #56: notifikasi in-app ke nasabah lewat log_notifikasi_mandiri
     * (dashboard Layanan Mandiri inti). Gagal tidak mengganggu transaksi.
     */
    private function notifWarga(MemberModel $member, string $judul, string $isi): void
    {
        try {
            $mandiri = DB::table('tweb_penduduk_mandiri')
                ->where('id_pend', $member->penduduk_id)
                ->where('config_id', identitas('id'))
                ->value('id');

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
}
