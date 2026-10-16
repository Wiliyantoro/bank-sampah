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
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Modules\BankSampah\Models\BankSampahMember;
use Modules\BankSampah\Models\BankSampahSampah;
use Modules\BankSampah\Models\BankSampahTransaksi;
use Modules\BankSampah\Models\BankSampahTransaksiDetail;
use Modules\BankSampah\Models\BankSampahReward;
use Modules\BankSampah\Models\BankSampahLokasi;
use Modules\BankSampah\Libraries\WhatsAppNotification;

class BankSampahTransaksiController extends Controller
{
    protected WhatsAppNotification $whatsapp;

    public function __construct(WhatsAppNotification $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Daftar transaksi (filtered per desa)
     */
    public function index(Request $request)
    {
        $query = BankSampahTransaksi::desa()->with('member');

        if ($request->has('tipe') && $request->tipe !== '') {
            $query->where('tipe', $request->tipe);
        }

        if ($request->has('tanggal_awal') && $request->tanggal_awal !== '') {
            $query->whereDate('created_at', '>=', $request->tanggal_awal);
        }

        if ($request->has('tanggal_akhir') && $request->tanggal_akhir !== '') {
            $query->whereDate('created_at', '<=', $request->tanggal_akhir);
        }

        if ($request->has('cari') && $request->cari !== '') {
            $cari = $request->cari;
            $safeCari = safe_like($cari);
            $query->where(function ($q) use ($safeCari) {
                $q->where('no_transaksi', 'LIKE', "%{$safeCari}%")
                  ->orWhereHas('member', function ($mq) use ($safeCari) {
                      $mq->where('nama', 'LIKE', "%{$safeCari}%");
                  });
            });
        }

        $transaksi = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('banksampah::backend.transaksi.index', compact('transaksi'));
    }

    /**
     * Form setor sampah
     */
    public function formSetor(Request $request)
    {
        $memberList = BankSampahMember::desa()->aktif()->orderBy('nama')->get();
        $sampahList = BankSampahSampah::desa()->aktif()->with('kategori')->orderBy('kategori_id')->orderBy('nama')->get();
        $lokasiList = BankSampahLokasi::desa()->aktif()->orderBy('nama')->get();

        $selectedMember = null;
        if ($request->has('member_id')) {
            $selectedMember = BankSampahMember::desa()->find($request->member_id);
        }

        return view('banksampah::backend.transaksi.form_setor', compact(
            'memberList', 'sampahList', 'lokasiList', 'selectedMember'
        ));
    }

    /**
     * Proses transaksi setor
     */
    public function prosesSetor(Request $request)
    {
        $request->validate([
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
            $member = BankSampahMember::desa()->findOrFail($request->member_id);
            $config = config('banksampah');

            $total = 0;
            $details = [];

            foreach ($request->item as $item) {
                $sampah = BankSampahSampah::desa()->findOrFail($item['sampah_id']);
                $subtotal = $item['berat'] * $sampah->harga_beli;
                $total += $subtotal;

                $details[] = [
                    'sampah_id' => $item['sampah_id'],
                    'berat'     => $item['berat'],
                    'harga'     => $sampah->harga_beli,
                    'subtotal'  => $subtotal,
                ];
            }

            $totalBerat = array_sum(array_column($request->item, 'berat'));
            $poinPerKg = $config['reward']['poin_per_kg'] ?? 10;
            $poinDidapat = (int) ($totalBerat * $poinPerKg);

            $transaksi = BankSampahTransaksi::create([
                'config_id'    => identitas('id'),
                'no_transaksi' => BankSampahTransaksi::generateNoTransaksi(),
                'member_id'    => $member->id,
                'lokasi_id'    => $request->lokasi_id,
                'tipe'         => 'setor',
                'total'        => $total,
                'bayar'        => $total,
                'poin'         => $poinDidapat,
                'keterangan'   => $request->keterangan,
                'user_id'      => auth()->id(),
            ]);

            foreach ($details as $detail) {
                BankSampahTransaksiDetail::create(array_merge($detail, [
                    'transaksi_id' => $transaksi->id,
                ]));

                $sampah = BankSampahSampah::find($detail['sampah_id']);
                $sampah->tambahStok($detail['berat']);
            }

            $member->tambahSaldo($total);

            if ($config['reward']['enabled'] && $poinDidapat > 0) {
                $member->tambahPoin($poinDidapat);

                BankSampahReward::create([
                    'config_id'    => identitas('id'),
                    'member_id'    => $member->id,
                    'transaksi_id' => $transaksi->id,
                    'tipe'         => 'add',
                    'poin'         => $poinDidapat,
                    'keterangan'   => 'Poin dari transaksi setor #' . $transaksi->no_transaksi,
                ]);
            }

            DB::commit();

            if (config('banksampah.whatsapp.enabled') && $member->no_hp) {
                $this->whatsapp->kirimNotifikasiSetor($member, $transaksi);
            }

            return redirect()->route('bank-sampah/transaksi/detail', $transaksi->id)
                ->with('success', 'Transaksi setor berhasil! Total: Rp ' . number_format($total, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            // BS-014: pesan exception bisa bocorkan SQL/tabel/path — log penuh, UI generik
            Log::error('[BankSampah] prosesSetor gagal', ['exception' => $e]);
            return redirect()->back()
                ->with('error', 'Gagal memproses transaksi. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Form tarik saldo
     */
    public function formTarik(Request $request)
    {
        $memberList = BankSampahMember::desa()->aktif()->orderBy('nama')->get();

        $selectedMember = null;
        if ($request->has('member_id')) {
            $selectedMember = BankSampahMember::desa()->find($request->member_id);
        }

        return view('banksampah::backend.transaksi.form_tarik', compact('memberList', 'selectedMember'));
    }

    /**
     * Proses transaksi tarik
     */
    public function prosesTarik(Request $request)
    {
        $request->validate([
            'member_id' => 'required|integer',
            'nominal'   => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();

        try {
            $member = BankSampahMember::desa()->findOrFail($request->member_id);

            if ($member->saldo < $request->nominal) {
                return redirect()->back()
                    ->with('error', 'Saldo tidak mencukupi. Saldo saat ini: Rp ' . number_format($member->saldo, 0, ',', '.'))
                    ->withInput();
            }

            $transaksi = BankSampahTransaksi::create([
                'config_id'    => identitas('id'),
                'no_transaksi' => BankSampahTransaksi::generateNoTransaksi(),
                'member_id'    => $member->id,
                'tipe'         => 'tarik',
                'total'        => $request->nominal,
                'bayar'        => $request->nominal,
                'keterangan'   => $request->keterangan,
                'user_id'      => auth()->id(),
            ]);

            if (! $member->kurangiSaldo($request->nominal)) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Saldo tidak mencukupi. Silakan coba lagi.')
                    ->withInput();
            }

            DB::commit();

            if (config('banksampah.whatsapp.enabled') && $member->no_hp) {
                $this->whatsapp->kirimNotifikasiTarik($member, $transaksi);
            }

            return redirect()->route('bank-sampah/transaksi/detail', $transaksi->id)
                ->with('success', 'Transaksi tarik berhasil! Nominal: Rp ' . number_format($request->nominal, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            // BS-014: pesan exception bisa bocorkan SQL/tabel/path — log penuh, UI generik
            Log::error('[BankSampah] prosesTarik gagal', ['exception' => $e]);
            return redirect()->back()
                ->with('error', 'Gagal memproses transaksi. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Detail transaksi
     */
    public function detail($id)
    {
        $transaksi = BankSampahTransaksi::desa()
            ->with(['member.lokasi', 'detail.sampah', 'lokasi'])
            ->findOrFail($id);

        return view('banksampah::backend.transaksi.detail', compact('transaksi'));
    }

    /**
     * Cetak struk transaksi
     */
    public function cetakStruk($id)
    {
        $transaksi = BankSampahTransaksi::desa()
            ->with(['member.lokasi', 'detail.sampah', 'lokasi'])
            ->findOrFail($id);

        $config = config('banksampah');

        if ($config['format_struk'] === 'thermal') {
            return view('banksampah::backend.transaksi.struk_thermal', compact('transaksi', 'config'));
        }

        return view('banksampah::backend.transaksi.struk_a4', compact('transaksi', 'config'));
    }

    /**
     * Get data member via AJAX
     */
    public function getMember(Request $request)
    {
        $member = BankSampahMember::desa()->findOrFail($request->id);

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
    public function getSampah(Request $request)
    {
        $sampah = BankSampahSampah::desa()->with('kategori')->findOrFail($request->id);

        return response()->json([
            'id'         => $sampah->id,
            'nama'       => $sampah->nama,
            'satuan'     => $sampah->satuan,
            'harga_beli' => $sampah->harga_beli,
            'kategori'   => $sampah->kategori->nama,
        ]);
    }
}
