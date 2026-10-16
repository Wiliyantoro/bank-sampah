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
use Modules\BankSampah\Models\BankSampahKategori;

class BankSampahKategoriController extends Controller
{
    /**
     * Daftar kategori (filtered per desa)
     */
    public function index(Request $request)
    {
        $query = BankSampahKategori::desa(); // ← Filter per desa

        if ($request->has('cari') && $request->cari !== '') {
            $query->where('nama', 'LIKE', '%' . safe_like($request->cari) . '%');
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('aktif', $request->status);
        }

        $kategori = $query->orderBy('nama')->paginate(15);

        return view('banksampah::backend.kategori.index', compact('kategori'));
    }

    /**
     * Form tambah kategori
     */
    public function create()
    {
        return view('banksampah::backend.kategori.form', [
            'kategori' => null,
            'action'   => 'tambah',
        ]);
    }

    /**
     * Simpan kategori baru (data custom desa)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        BankSampahKategori::create([
            'config_id'  => identitas('id'), // ← Per desa
            'nama'       => $request->nama,
            'keterangan' => $request->keterangan,
            'aktif'      => $request->input('aktif', 1),
            'is_default' => 0, // ← Data custom desa
            'sumber'     => 'desa',
        ]);

        return redirect()->route('bank-sampah/kategori')
            ->with('success', 'Kategori berhasil ditambahkan');
    }

    /**
     * Form edit kategori
     */
    public function edit($id)
    {
        $kategori = BankSampahKategori::desa()->findOrFail($id);

        return view('banksampah::backend.kategori.form', [
            'kategori' => $kategori,
            'action'   => 'ubah',
        ]);
    }

    /**
     * Update kategori (boleh edit data bawaan, tapi tidak mengubah flag is_default)
     */
    public function update(Request $request, $id)
    {
        $kategori = BankSampahKategori::desa()->findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        $kategori->update([
            'nama'       => $request->nama,
            'keterangan' => $request->keterangan,
            'aktif'      => $request->input('aktif', 1),
            // is_default TIDAK diubah — tetap seperti semula
        ]);

        return redirect()->route('bank-sampah/kategori')
            ->with('success', 'Kategori berhasil diubah');
    }

    /**
     * Hapus kategori (HANYA data custom desa yang boleh dihapus)
     */
    public function destroy($id)
    {
        $kategori = BankSampahKategori::desa()->findOrFail($id);

        // Data bawaan sistem TIDAK boleh dihapus
        if ($kategori->isDefault()) {
            return redirect()->route('bank-sampah/kategori')
                ->with('error', 'Data bawaan sistem tidak bisa dihapus. Anda hanya bisa mengubah status aktif/nonaktif.');
        }

        // Cek apakah ada sampah yang terhubung
        if ($kategori->sampah()->count() > 0) {
            return redirect()->route('bank-sampah/kategori')
                ->with('error', 'Tidak bisa hapus, masih ada jenis sampah di kategori ini');
        }

        $kategori->delete();

        return redirect()->route('bank-sampah/kategori')
            ->with('success', 'Kategori berhasil dihapus');
    }
}
