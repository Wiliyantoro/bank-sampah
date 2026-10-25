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

use Modules\BankSampah\Models\LokasiModel;

class LokasiController extends AdminModulController
{
    public $moduleName    = 'BankSampah';
    public $modul_ini     = 'bank-sampah';
    public $sub_modul_ini = 'bank-sampah-lokasi';

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    public function index()
    {
        if (request()->ajax()) {
            return datatables()->of(LokasiModel::desa()->withCount(['member', 'transaksi']))
                ->addIndexColumn()
                ->addColumn('aksi', static function ($row): string {
                    $aksi = '';
                    if (can('u')) {
                        $aksi .= '<a href="' . ci_route('bank-sampah.lokasi.form', $row->id) . '" class="btn btn-warning btn-sm" title="Ubah"><i class="fa fa-edit"></i></a> ';
                    }
                    if (can('h') && ! $row->isDefault()) {
                        $aksi .= '<a href="#" data-href="' . ci_route('bank-sampah.lokasi.delete', $row->id) . '" class="btn btn-danger btn-sm btn-hapus" title="Hapus"><i class="fa fa-trash"></i></a>';
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

        return view('banksampah::backend.lokasi.index');
    }

    /**
     * Form tambah/ubah lokasi ($id null = tambah)
     */
    public function form($id = null)
    {
        $lokasi = $id ? LokasiModel::desa()->findOrFail($id) : null;

        return view('banksampah::backend.lokasi.form', [
            'lokasi'      => $lokasi,
            'form_action' => $id
                ? ci_route('bank-sampah.lokasi.update', $id)
                : ci_route('bank-sampah.lokasi.insert'),
        ]);
    }

    public function insert()
    {
        $configId = identitas('id');

        request()->validate([
            'nama'   => 'required|string|max:150',
            'kode'   => "required|string|max:20|unique:bank_sampah_lokasi,kode,NULL,id,config_id,{$configId}",
            'alamat' => 'nullable|string',
            // BS-040: batas panjang mengikuti kolom DB (penanggung_jawab 150,
            // no_hp 20) — tanpa ini MySQL strict melempar error 1406 (500)
            'penanggung_jawab' => 'nullable|string|max:150',
            'no_hp'            => 'nullable|string|max:20',
        ]);

        LokasiModel::create([
            'nama'             => request('nama'),
            'kode'             => request('kode'),
            'alamat'           => request('alamat'),
            'penanggung_jawab' => request('penanggung_jawab'),
            'no_hp'            => request('no_hp'),
            'aktif'            => request('aktif', 1),
            'is_default'       => 0, // data custom desa
            'sumber'           => 'desa',
        ]);

        return redirect_with('success', 'Lokasi/TPS berhasil ditambahkan');
    }

    public function update($id = null)
    {
        $lokasi   = LokasiModel::desa()->findOrFail($id);
        $configId = identitas('id');

        request()->validate([
            'nama'   => 'required|string|max:150',
            'kode'   => "required|string|max:20|unique:bank_sampah_lokasi,kode,{$id},id,config_id,{$configId}",
            'alamat' => 'nullable|string',
            // BS-040: lihat komentar insert()
            'penanggung_jawab' => 'nullable|string|max:150',
            'no_hp'            => 'nullable|string|max:20',
        ]);

        $lokasi->update([
            'nama'             => request('nama'),
            'kode'             => request('kode'),
            'alamat'           => request('alamat'),
            'penanggung_jawab' => request('penanggung_jawab'),
            'no_hp'            => request('no_hp'),
            'aktif'            => request('aktif', 1),
        ]);

        return redirect_with('success', 'Lokasi/TPS berhasil diubah');
    }

    public function delete($id = null): void
    {
        $lokasi = LokasiModel::desa()->findOrFail($id);

        if ($lokasi->isDefault()) {
            redirect_with('error', 'Data bawaan sistem tidak bisa dihapus');
            return;
        }

        if ($lokasi->member()->exists()) {
            redirect_with('error', 'Tidak bisa hapus, masih ada nasabah di lokasi ini');
            return;
        }

        $lokasi->delete();

        redirect_with('success', 'Lokasi/TPS berhasil dihapus');
    }
}
