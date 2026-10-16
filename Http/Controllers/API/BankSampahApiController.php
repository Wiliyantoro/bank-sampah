<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 *
 */

namespace Modules\BankSampah\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\BankSampah\Models\BankSampahMember;
use Modules\BankSampah\Models\BankSampahTransaksi;
use Modules\BankSampah\Models\BankSampahSampah;
use Modules\BankSampah\Models\BankSampahKategori;

class BankSampahApiController extends Controller
{
    /**
     * Login member via API
     */
    public function login(Request $request)
    {
        $request->validate([
            'no_member' => 'required|string',
            'no_hp'     => 'required|string',
        ]);

        $member = BankSampahMember::where('no_member', $request->no_member)
            ->where('no_hp', $request->no_hp)
            ->where('aktif', 1)
            ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor member atau nomor HP tidak valid',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'        => $member->id,
                'no_member' => $member->no_member,
                'nama'      => $member->nama,
                'saldo'     => $member->saldo,
                'poin'      => $member->poin,
            ],
        ]);
    }

    /**
     * Riwayat transaksi member
     */
    public function riwayatTransaksi(Request $request, $memberId)
    {
        $transaksi = BankSampahTransaksi::where('member_id', $memberId)
            ->with('detail.sampah')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $transaksi,
        ]);
    }

    /**
     * Daftar jenis sampah + harga
     */
    public function daftarSampah()
    {
        $sampah = BankSampahSampah::aktif()
            ->with('kategori')
            ->orderBy('kategori_id')
            ->orderBy('nama')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $sampah,
        ]);
    }

    /**
     * Info saldo member
     */
    public function saldo($memberId)
    {
        $member = BankSampahMember::findOrFail($memberId);

        return response()->json([
            'success' => true,
            'data'    => [
                'no_member' => $member->no_member,
                'nama'      => $member->nama,
                'saldo'     => $member->saldo,
                'poin'      => $member->poin,
            ],
        ]);
    }

    /**
     * Daftar kategori sampah
     */
    public function daftarKategori()
    {
        $kategori = BankSampahKategori::aktif()->withCount('sampah')->get();

        return response()->json([
            'success' => true,
            'data'    => $kategori,
        ]);
    }
}
