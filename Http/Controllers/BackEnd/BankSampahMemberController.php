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
use Modules\BankSampah\Models\BankSampahMember;
use Modules\BankSampah\Models\BankSampahLokasi;
use Modules\BankSampah\Models\BankSampahTransaksi;

class BankSampahMemberController extends Controller
{
    /**
     * Daftar nasabah (filtered per desa)
     */
    public function index(Request $request)
    {
        $query = BankSampahMember::desa()->with('lokasi');

        if ($request->has('cari') && $request->cari !== '') {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('nama', 'LIKE', '%' . safe_like($cari) . '%')
                  ->orWhere('no_member', 'LIKE', '%' . safe_like($cari) . '%')
                  ->orWhere('no_hp', 'LIKE', '%' . safe_like($cari) . '%');
            });
        }

        if ($request->has('lokasi_id') && $request->lokasi_id !== '') {
            $query->where('lokasi_id', $request->lokasi_id);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('aktif', $request->status);
        }

        $member = $query->orderBy('nama')->paginate(15);
        $lokasiList = BankSampahLokasi::desa()->aktif()->orderBy('nama')->get();

        return view('banksampah::backend.member.index', compact('member', 'lokasiList'));
    }

    /**
     * Form tambah nasabah
     */
    public function create()
    {
        $config = config('banksampah');
        $noMember = BankSampahMember::generateNoMember($config['prefix_no_member']);
        $lokasiList = BankSampahLokasi::desa()->aktif()->orderBy('nama')->get();

        return view('banksampah::backend.member.form', [
            'member'     => null,
            'noMember'   => $noMember,
            'lokasiList' => $lokasiList,
            'action'     => 'tambah',
        ]);
    }

    /**
     * Simpan nasabah baru (unique per desa)
     */
    public function store(Request $request)
    {
        $configId = identitas('id');

        $request->validate([
            'no_member'   => "required|string|max:20|unique:bank_sampah_member,no_member,NULL,id,config_id,{$configId}",
            'nama'        => 'required|string|max:150',
            'no_hp'       => 'nullable|string|max:20',
            'alamat'      => 'nullable|string',
            // BS-015: ID eksternal wajib ter-scope desa agar tidak bisa
            // dilink ke data penduduk/lokasi desa lain
            'penduduk_id' => "nullable|integer|exists:tweb_penduduk,id,config_id,{$configId}",
            'lokasi_id'   => "nullable|integer|exists:bank_sampah_lokasi,id,config_id,{$configId}",
        ]);

        BankSampahMember::create([
            'config_id'   => $configId,
            'no_member'   => $request->no_member,
            'nama'        => $request->nama,
            'no_hp'       => $request->no_hp,
            'alamat'      => $request->alamat,
            'penduduk_id' => $request->penduduk_id,
            'lokasi_id'   => $request->lokasi_id,
            'aktif'       => $request->input('aktif', 1),
        ]);

        return redirect()->route('bank-sampah/member')
            ->with('success', 'Nasabah berhasil ditambahkan');
    }

    /**
     * Form edit nasabah
     */
    public function edit($id)
    {
        $member = BankSampahMember::desa()->findOrFail($id);
        $lokasiList = BankSampahLokasi::desa()->aktif()->orderBy('nama')->get();

        return view('banksampah::backend.member.form', [
            'member'     => $member,
            'noMember'   => $member->no_member,
            'lokasiList' => $lokasiList,
            'action'     => 'ubah',
        ]);
    }

    /**
     * Update nasabah
     */
    public function update(Request $request, $id)
    {
        $configId = identitas('id');
        $member = BankSampahMember::desa()->findOrFail($id);

        $request->validate([
            'nama'        => 'required|string|max:150',
            'no_hp'       => 'nullable|string|max:20',
            'alamat'      => 'nullable|string',
            // BS-015: ID eksternal wajib ter-scope desa
            'penduduk_id' => 'nullable|integer|exists:tweb_penduduk,id,config_id,' . $configId,
            'lokasi_id'   => 'nullable|integer|exists:bank_sampah_lokasi,id,config_id,' . $configId,
        ]);

        $member->update([
            'nama'        => $request->nama,
            'no_hp'       => $request->no_hp,
            'alamat'      => $request->alamat,
            'penduduk_id' => $request->penduduk_id,
            'lokasi_id'   => $request->lokasi_id,
            'aktif'       => $request->input('aktif', 1),
        ]);

        return redirect()->route('bank-sampah/member')
            ->with('success', 'Nasabah berhasil diubah');
    }

    /**
     * Hapus nasabah
     */
    public function destroy($id)
    {
        $member = BankSampahMember::desa()->findOrFail($id);

        if ($member->transaksi()->count() > 0) {
            return redirect()->route('bank-sampah/member')
                ->with('error', 'Tidak bisa hapus, nasabah masih memiliki transaksi');
        }

        $member->delete();

        return redirect()->route('bank-sampah/member')
            ->with('success', 'Nasabah berhasil dihapus');
    }

    /**
     * Detail nasabah + riwayat transaksi
     */
    public function detail($id)
    {
        $member = BankSampahMember::desa()->with(['lokasi', 'transaksi.detail.sampah'])
            ->findOrFail($id);

        $riwayatTransaksi = BankSampahTransaksi::desa()
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
        $member = BankSampahMember::desa()->with('lokasi')->findOrFail($id);
        $config = config('banksampah');

        // BS-023: QR memuat URL dashboard (halaman login) — bukan data member.
        // no_member = separuh kredensial login warga; tidak boleh dikodekan di QR.
        $shareUrl = url('bank-sampah/warga');

        return view('banksampah::backend.member.kartu', compact('member', 'config', 'shareUrl'));
    }

    /**
     * AJAX: Cari data penduduk untuk autocomplete
     */
    public function ajaxPenduduk(Request $request)
    {
        $cari = $request->input('q', '');
        $limit = min((int) $request->input('limit', 20), 50);

        $query = \DB::table('tweb_penduduk')
            ->select('id', 'nama', 'nik', 'alamat_sekarang', 'telepon', 'sex')
            ->where('status_dasar', 1)
            ->orderBy('nama');

        if ($cari !== '') {
            $safeCari = safe_like($cari);
            $query->where(function ($q) use ($safeCari) {
                $q->where('nama', 'LIKE', "%{$safeCari}%")
                  ->orWhere('nik', 'LIKE', "%{$safeCari}%");
            });
        }

        $results = $query->limit($limit)->get()->map(function ($item) {
            $jk = $item->sex == 1 ? 'Laki-laki' : ($item->sex == 2 ? 'Perempuan' : '-');
            return [
                'id'      => $item->id,
                'nama'    => $item->nama,
                'nik'     => $item->nik,
                'alamat'  => $item->alamat_sekarang ?? '',
                'telepon' => $item->telepon ?? '',
                'jk'      => $jk,
                'text'    => $item->nik . ' - ' . $item->nama,
            ];
        });

        return response()->json(['results' => $results, 'count' => $results->count()]);
    }

    /**
     * AJAX: Detail penduduk by ID
     */
    public function ajaxPendudukDetail($id)
    {
        $p = \DB::table('tweb_penduduk')
            ->select('id', 'nama', 'nik', 'alamat_sekarang', 'telepon', 'sex')
            ->where('id', $id)
            ->where('status_dasar', 1)
            ->first();

        if (! $p) {
            return response()->json(['error' => 'Tidak ditemukan'], 404);
        }

        $jk = $p->sex == 1 ? 'Laki-laki' : ($p->sex == 2 ? 'Perempuan' : '-');
        return response()->json([
            'id' => $p->id, 'nama' => $p->nama, 'nik' => $p->nik,
            'alamat' => $p->alamat_sekarang ?? '', 'telepon' => $p->telepon ?? '', 'jk' => $jk,
        ]);
    }
}
