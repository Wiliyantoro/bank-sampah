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

use Illuminate\Validation\Rule;
use Modules\BankSampah\Models\KategoriModel;
use Modules\BankSampah\Models\SampahModel;

class SampahController extends AdminModulController
{
    public $moduleName    = 'BankSampah';
    public $modul_ini     = 'bank-sampah';
    public $sub_modul_ini = 'bank-sampah-sampah';
    public $kategori_pengaturan = 'Bank Sampah';
    public $aliasController   = 'bank-sampah.jenis-sampah';

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    public function index()
    {
        if (request()->ajax()) {
            return datatables()->of(SampahModel::query()->with('kategori'))
                ->addIndexColumn()
                ->addColumn('aksi', static function ($row): string {
                    $aksi = '';
                    if (can('u')) {
                        $aksi .= '<a href="' . ci_route('bank-sampah.jenis-sampah.form', $row->id) . '" class="btn btn-warning btn-sm" title="Ubah"><i class="fa fa-edit"></i></a> ';
                    }
                    if (can('h') && ! $row->isDefault()) {
                        $aksi .= '<a href="#" data-href="' . ci_route('bank-sampah.jenis-sampah.delete', $row->id) . '" class="btn btn-danger btn-sm btn-hapus" title="Hapus"><i class="fa fa-trash"></i></a>';
                    }
                    return $aksi;
                })
                ->editColumn('is_default', static function ($row): string {
                    return $row->isDefault()
                        ? '<span class="label label-info">Bawaan</span>'
                        : '<span class="label label-success">Desa</span>';
                })
                ->rawColumns(['aksi', 'is_default'])
                ->make();
        }

        $kategoriList = KategoriModel::query()->aktif()->orderBy('nama')->get();

        return view('banksampah::backend.sampah.index', compact('kategoriList'));
    }

    /**
     * Form tambah/ubah jenis sampah ($id null = tambah)
     */
    public function form($id = null)
    {
        $sampah      = $id ? SampahModel::query()->findOrFail($id) : null;
        $kategoriList = KategoriModel::query()->aktif()->orderBy('nama')->get();

        return view('banksampah::backend.sampah.form', [
            'sampah'       => $sampah,
            'kategoriList' => $kategoriList,
            'form_action'  => $id
                ? ci_route('bank-sampah.jenis-sampah.update', $id)
                : ci_route('bank-sampah.jenis-sampah.insert'),
        ]);
    }

    public function insert()
    {
        isCan('u');
        request()->validate([
            'nama'        => 'required|string|max:150',
            // BS-034: kategori wajib ada & milik desa aktif — tanpa ini
            // id ngawur melempar QueryException FK (500) dan id milik
            // desa lain bisa lolos (referensi lintas desa)
            'kategori_id' => ['required', Rule::exists('bank_sampah_kategori', 'id')
                ->where('config_id', identitas('id'))],
            'satuan'      => 'required|string|max:20',
            'harga_beli'  => 'required|numeric|min:0',
            'harga_jual'  => 'nullable|numeric|min:0',
        ]);

        SampahModel::create([
            'kategori_id' => request('kategori_id'),
            'nama'        => request('nama'),
            'satuan'      => request('satuan'),
            'harga_beli'  => request('harga_beli'),
            'harga_jual'  => request('harga_jual', 0),
            'aktif'       => request('aktif', 1),
            'is_default'  => 0, // data custom desa
            'sumber'      => 'desa',
        ]);

        return redirect_with('success', 'Jenis sampah berhasil ditambahkan', ci_route('bank-sampah.jenis-sampah'));
    }

    public function update($id = null)
    {
        isCan('u');
        $sampah = SampahModel::query()->findOrFail($id);

        request()->validate([
            'nama'        => 'required|string|max:150',
            // BS-034: lihat komentar insert()
            'kategori_id' => ['required', Rule::exists('bank_sampah_kategori', 'id')
                ->where('config_id', identitas('id'))],
            'satuan'      => 'required|string|max:20',
            'harga_beli'  => 'required|numeric|min:0',
            'harga_jual'  => 'nullable|numeric|min:0',
        ]);

        $sampah->update([
            'kategori_id' => request('kategori_id'),
            'nama'        => request('nama'),
            'satuan'      => request('satuan'),
            'harga_beli'  => request('harga_beli'),
            'harga_jual'  => request('harga_jual', 0),
            'aktif'       => request('aktif', 1),
            // is_default TIDAK diubah
        ]);

        return redirect_with('success', 'Jenis sampah berhasil diubah', ci_route('bank-sampah.jenis-sampah'));
    }

    public function delete($id = null): void
    {
        isCan('d');
        $sampah = SampahModel::query()->findOrFail($id);

        // Data bawaan sistem tidak boleh dihapus
        if ($sampah->isDefault()) {
            redirect_with('error', 'Data bawaan sistem tidak bisa dihapus. Anda hanya bisa mengubah status aktif/nonaktif.', ci_route('bank-sampah.jenis-sampah'));
            return;
        }

        // BS-033: FK transaksi_detail.sampah_id = ON DELETE CASCADE — hapus jenis
        // sampah yang punya riwayat akan ikut menghapus baris detail transaksi
        // (berat/harga/subtotal snapshot) secara diam-diam. Tolak, arahkan
        // nonaktifkan saja.
        if ($sampah->transaksiDetail()->exists()) {
            redirect_with('error', 'Tidak bisa hapus, jenis sampah ini sudah memiliki riwayat transaksi. Nonaktifkan saja bila tidak dipakai lagi.', ci_route('bank-sampah.jenis-sampah'));
            return;
        }

        $sampah->delete();

        redirect_with('success', 'Jenis sampah berhasil dihapus', ci_route('bank-sampah.jenis-sampah'));
    }
}
