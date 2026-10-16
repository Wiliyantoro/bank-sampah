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
use Modules\BankSampah\Models\BankSampahLokasi;

class BankSampahLokasiController extends Controller
{
    /**
     * Daftar lokasi/TPS (filtered per desa)
     */
    public function index(Request $request)
    {
        $query = BankSampahLokasi::desa()->withCount(['member', 'transaksi']);

        if ($request->has('cari') && $request->cari !== '') {
            $cari = $request->cari;
            $safeCari = safe_like($cari);
            $query->where(function ($q) use ($safeCari) {
                $q->where('nama', 'LIKE', "%{$safeCari}%")
                  ->orWhere('kode', 'LIKE', "%{$safeCari}%");
            });
        }

        $lokasi = $query->orderBy('nama')->paginate(15);

        return view('banksampah::backend.lokasi.index', compact('lokasi'));
    }

    /**
     * Form tambah lokasi
     */
    public function create()
    {
        return view('banksampah::backend.lokasi.form', [
            'lokasi' => null,
            'action' => 'tambah',
        ]);
    }

    /**
     * Simpan lokasi baru (unique per desa)
     */
    public function store(Request $request)
    {
        $configId = identitas('id');

        $request->validate([
            'nama'   => 'required|string|max:150',
            'kode'   => "required|string|max:20|unique:bank_sampah_lokasi,kode,NULL,id,config_id,{$configId}",
            'alamat' => 'nullable|string',
        ]);

        BankSampahLokasi::create([
            'config_id'           => $configId,
            'nama'                => $request->nama,
            'kode'                => $request->kode,
            'alamat'              => $request->alamat,
            'penanggung_jawab'    => $request->penanggung_jawab,
            'no_hp'               => $request->no_hp,
            'aktif'               => $request->input('aktif', 1),
            'is_default'          => 0,
            'sumber'              => 'desa',
        ]);

        return redirect()->route('bank-sampah/lokasi')
            ->with('success', 'Lokasi/TPS berhasil ditambahkan');
    }

    /**
     * Form edit lokasi
     */
    public function edit($id)
    {
        $lokasi = BankSampahLokasi::desa()->findOrFail($id);

        return view('banksampah::backend.lokasi.form', [
            'lokasi' => $lokasi,
            'action' => 'ubah',
        ]);
    }

    /**
     * Update lokasi
     */
    public function update(Request $request, $id)
    {
        $lokasi = BankSampahLokasi::desa()->findOrFail($id);
        $configId = identitas('id');

        $request->validate([
            'nama' => 'required|string|max:150',
            'kode' => "required|string|max:20|unique:bank_sampah_lokasi,kode,{$id},id,config_id,{$configId}",
        ]);

        $lokasi->update([
            'nama'             => $request->nama,
            'kode'             => $request->kode,
            'alamat'           => $request->alamat,
            'penanggung_jawab' => $request->penanggung_jawab,
            'no_hp'            => $request->no_hp,
            'aktif'            => $request->input('aktif', 1),
        ]);

        return redirect()->route('bank-sampah/lokasi')
            ->with('success', 'Lokasi/TPS berhasil diubah');
    }

    /**
     * Hapus lokasi (hanya data custom desa)
     */
    public function destroy($id)
    {
        $lokasi = BankSampahLokasi::desa()->findOrFail($id);

        if ($lokasi->isDefault()) {
            return redirect()->route('bank-sampah/lokasi')
                ->with('error', 'Data bawaan sistem tidak bisa dihapus');
        }

        if ($lokasi->member()->count() > 0) {
            return redirect()->route('bank-sampah/lokasi')
                ->with('error', 'Tidak bisa hapus, masih ada nasabah di lokasi ini');
        }

        $lokasi->delete();

        return redirect()->route('bank-sampah/lokasi')
            ->with('success', 'Lokasi/TPS berhasil dihapus');
    }
}
