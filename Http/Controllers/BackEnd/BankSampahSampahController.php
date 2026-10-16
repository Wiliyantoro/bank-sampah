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

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\BankSampah\Models\BankSampahSampah;
use Modules\BankSampah\Models\BankSampahKategori;

class BankSampahSampahController extends Controller
{
    /**
     * Daftar jenis sampah (filtered per desa)
     */
    public function index(Request $request)
    {
        $query = BankSampahSampah::with('kategori')->desa(); // ← Filter per desa

        if ($request->has('cari') && $request->cari !== '') {
            $query->where('nama', 'LIKE', '%' . safe_like($request->cari) . '%');
        }

        if ($request->has('kategori_id') && $request->kategori_id !== '') {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('aktif', $request->status);
        }

        $sampah = $query->orderBy('kategori_id')->orderBy('nama')->paginate(15);
        $kategoriList = BankSampahKategori::desa()->aktif()->orderBy('nama')->get();

        return view('banksampah::backend.sampah.index', compact('sampah', 'kategoriList'));
    }

    /**
     * Form tambah jenis sampah
     */
    public function create()
    {
        $kategoriList = BankSampahKategori::desa()->aktif()->orderBy('nama')->get();

        return view('banksampah::backend.sampah.form', [
            'sampah'      => null,
            'kategoriList' => $kategoriList,
            'action'      => 'tambah',
        ]);
    }

    /**
     * Simpan jenis sampah baru (data custom desa)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'        => 'required|string|max:150',
            'kategori_id' => 'required|integer',
            'satuan'      => 'required|string|max:20',
            'harga_beli'  => 'required|numeric|min:0',
            'harga_jual'  => 'nullable|numeric|min:0',
        ]);

        BankSampahSampah::create([
            'config_id'   => identitas('id'), // ← Per desa
            'kategori_id' => $request->kategori_id,
            'nama'        => $request->nama,
            'satuan'      => $request->satuan,
            'harga_beli'  => $request->harga_beli,
            'harga_jual'  => $request->input('harga_jual', 0),
            'aktif'       => $request->input('aktif', 1),
            'is_default'  => 0, // ← Data custom desa
            'sumber'      => 'desa',
        ]);

        return redirect()->route('bank-sampah/sampah')
            ->with('success', 'Jenis sampah berhasil ditambahkan');
    }

    /**
     * Form edit jenis sampah
     */
    public function edit($id)
    {
        $sampah = BankSampahSampah::desa()->findOrFail($id);
        $kategoriList = BankSampahKategori::desa()->aktif()->orderBy('nama')->get();

        return view('banksampah::backend.sampah.form', [
            'sampah'       => $sampah,
            'kategoriList' => $kategoriList,
            'action'       => 'ubah',
        ]);
    }

    /**
     * Update jenis sampah (boleh edit data bawaan)
     */
    public function update(Request $request, $id)
    {
        $sampah = BankSampahSampah::desa()->findOrFail($id);

        $request->validate([
            'nama'        => 'required|string|max:150',
            'kategori_id' => 'required|integer',
            'satuan'      => 'required|string|max:20',
            'harga_beli'  => 'required|numeric|min:0',
            'harga_jual'  => 'nullable|numeric|min:0',
        ]);

        $sampah->update([
            'kategori_id' => $request->kategori_id,
            'nama'        => $request->nama,
            'satuan'      => $request->satuan,
            'harga_beli'  => $request->harga_beli,
            'harga_jual'  => $request->input('harga_jual', 0),
            'aktif'       => $request->input('aktif', 1),
            // is_default TIDAK diubah
        ]);

        return redirect()->route('bank-sampah/sampah')
            ->with('success', 'Jenis sampah berhasil diubah');
    }

    /**
     * Hapus jenis sampah (HANYA data custom desa)
     */
    public function destroy($id)
    {
        $sampah = BankSampahSampah::desa()->findOrFail($id);

        // Data bawaan sistem TIDAK boleh dihapus
        if ($sampah->isDefault()) {
            return redirect()->route('bank-sampah/sampah')
                ->with('error', 'Data bawaan sistem tidak bisa dihapus. Anda hanya bisa mengubah status aktif/nonaktif.');
        }

        $sampah->delete();

        return redirect()->route('bank-sampah/sampah')
            ->with('success', 'Jenis sampah berhasil dihapus');
    }
}
