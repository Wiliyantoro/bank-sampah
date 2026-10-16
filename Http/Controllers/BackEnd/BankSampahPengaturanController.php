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

namespace Modules\BankSampah\Http\Controllers\BackEnd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\BankSampah\Models\BankSampahWargaSetting;

class BankSampahPengaturanController extends Controller
{
    /**
     * Halaman pengaturan module
     */
    public function index(Request $request)
    {
        $configId = identitas('id');
        $settings = BankSampahWargaSetting::getSettings($configId);

        return view('banksampah::backend.pengaturan.index', compact('settings'));
    }

    /**
     * Simpan pengaturan dashboard warga
     */
    public function updateWarga(Request $request)
    {
        $configId = identitas('id');

        $request->validate([
            'warga_aktif'          => 'required|in:0,1',
            'show_saldo'           => 'required|in:0,1',
            'show_riwayat'         => 'required|in:0,1',
            'show_poin'            => 'required|in:0,1',
            'require_pin'          => 'required|in:0,1',
            'max_riwayat'          => 'required|integer|min:1|max:50',
            'pesan_selamat_datang' => 'nullable|string|max:500',
            'pesan_footer'         => 'nullable|string|max:500',
        ]);

        $settings = BankSampahWargaSetting::getSettings($configId);

        $settings->update([
            'warga_aktif'          => $request->input('warga_aktif', 0),
            'show_saldo'           => $request->input('show_saldo', 0),
            'show_riwayat'         => $request->input('show_riwayat', 0),
            'show_poin'            => $request->input('show_poin', 0),
            'require_pin'          => $request->input('require_pin', 0),
            'max_riwayat'          => $request->input('max_riwayat', 10),
            'pesan_selamat_datang' => $request->pesan_selamat_datang,
            'pesan_footer'         => $request->pesan_footer,
        ]);

        return redirect()->route('bank-sampah/pengaturan')
            ->with('success', 'Pengaturan dashboard warga berhasil disimpan');
    }
}
