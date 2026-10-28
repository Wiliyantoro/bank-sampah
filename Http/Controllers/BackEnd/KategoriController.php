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

class KategoriController extends AdminModulController
{
    public $moduleName    = 'BankSampah';
    public $modul_ini     = 'bank-sampah';
    public $sub_modul_ini = 'bank-sampah-kategori';
    public $kategori_pengaturan = 'Bank Sampah';
    public $aliasController   = 'bank-sampah.kategori-sampah';

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    public function index()
    {
        if (request()->ajax()) {
            return datatables()->of(KategoriModel::query())
                ->addIndexColumn()
                ->addColumn('aksi', static function ($row): string {
                    $aksi = '';
                    if (can('u')) {
                        $aksi .= '<a href="' . ci_route('bank-sampah.kategori-sampah.form', $row->id) . '" class="btn btn-warning btn-sm" title="Ubah"><i class="fa fa-edit"></i></a> ';
                    }
                    if (can('h') && ! $row->isDefault()) {
                        $aksi .= '<a href="#" data-href="' . ci_route('bank-sampah.kategori-sampah.delete', $row->id) . '" class="btn btn-danger btn-sm btn-hapus" title="Hapus"><i class="fa fa-trash"></i></a>';
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

        return view('banksampah::backend.kategori.index');
    }

    /**
     * Form tambah/ubah kategori ($id null = tambah)
     */
    public function form($id = null)
    {
        $kategori = $id ? KategoriModel::query()->findOrFail($id) : null;

        return view('banksampah::backend.kategori.form', [
            'kategori'    => $kategori,
            'form_action' => $id
                ? ci_route('bank-sampah.kategori-sampah.update', $id)
                : ci_route('bank-sampah.kategori-sampah.insert'),
        ]);
    }

    public function insert()
    {
        isCan('u');
        request()->validate([
            'nama'       => 'required|string|max:150',
            'keterangan' => 'nullable|string|max:255',
        ]);

        KategoriModel::create([
            'nama'       => request('nama'),
            'keterangan' => request('keterangan'),
            'aktif'      => request('aktif', 1),
            'is_default' => 0, // data custom desa
            'sumber'     => 'desa',
        ]);

        return redirect_with('success', 'Kategori sampah berhasil ditambahkan', ci_route('bank-sampah.kategori-sampah'));
    }

    public function update($id = null)
    {
        isCan('u');
        $kategori = KategoriModel::query()->findOrFail($id);

        request()->validate([
            'nama'       => 'required|string|max:150',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $kategori->update([
            'nama'       => request('nama'),
            'keterangan' => request('keterangan'),
            'aktif'      => request('aktif', 1),
            // is_default TIDAK diubah
        ]);

        return redirect_with('success', 'Kategori sampah berhasil diubah', ci_route('bank-sampah.kategori-sampah'));
    }

    public function delete($id = null): void
    {
        isCan('d');
        $kategori = KategoriModel::query()->findOrFail($id);

        // Data bawaan sistem tidak boleh dihapus
        if ($kategori->isDefault()) {
            redirect_with('error', 'Data bawaan sistem tidak bisa dihapus. Anda hanya bisa mengubah status aktif/nonaktif.', ci_route('bank-sampah.kategori-sampah'));
            return;
        }

        // Kategori yang punya jenis sampah tidak boleh dihapus langsung
        if ($kategori->sampah()->exists()) {
            redirect_with('error', 'Tidak bisa hapus, kategori ini masih memiliki jenis sampah.', ci_route('bank-sampah.kategori-sampah'));
            return;
        }

        $kategori->delete();

        redirect_with('success', 'Kategori sampah berhasil dihapus', ci_route('bank-sampah.kategori-sampah'));
    }
}
