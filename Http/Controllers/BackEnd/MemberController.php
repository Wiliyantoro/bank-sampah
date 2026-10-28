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
use Modules\BankSampah\Models\MemberModel;
use Modules\BankSampah\Models\TransaksiModel;
use Modules\BankSampah\Http\Requests\MemberRequest;
use Modules\BankSampah\Services\IdentitasBankSampah;

class MemberController extends AdminModulController
{
    public $moduleName    = 'BankSampah';
    public $modul_ini     = 'bank-sampah';
    public $sub_modul_ini = 'bank-sampah-member';
    public $kategori_pengaturan = 'Bank Sampah';
    public $aliasController   = 'bank-sampah.member';

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    /**
     * Daftar nasabah (filtered per desa)
     */
    public function index()
    {
        if (request()->ajax()) {
            return datatables()->of(MemberModel::query()->with('lokasi'))
                ->addIndexColumn()
                ->addColumn('aksi', static function ($row): string {
                    $aksi = '<a href="' . ci_route('bank-sampah.member.detail', $row->id) . '" class="btn btn-default btn-sm" title="Detail"><i class="fa fa-eye"></i></a> ';
                    if (can('u')) {
                        $aksi .= '<a href="' . ci_route('bank-sampah.member.form', $row->id) . '" class="btn btn-warning btn-sm" title="Ubah"><i class="fa fa-edit"></i></a> ';
                    }
                    if (can('h')) {
                        $aksi .= '<a href="' . ci_route('bank-sampah.member.kartu', $row->id) . '" class="btn btn-default btn-sm" title="Cetak Kartu"><i class="fa fa-id-card"></i></a> ';
                        if (! $row->transaksi()->exists()) {
                            $aksi .= '<a href="#" data-href="' . ci_route('bank-sampah.member.delete', $row->id) . '" class="btn btn-danger btn-sm btn-hapus" title="Hapus"><i class="fa fa-trash"></i></a>';
                        }
                    }
                    return $aksi;
                })
                ->rawColumns(['aksi'])
                ->make();
        }

        $lokasiList = LokasiModel::query()->aktif()->orderBy('nama')->get();

        return view('banksampah::backend.member.index', compact('lokasiList'));
    }

    /**
     * Form tambah/ubah nasabah ($id null = tambah)
     */
    public function form($id = null)
    {
        $member    = $id ? MemberModel::query()->findOrFail($id) : null;
        $prefix    = setting('bank_sampah_prefix_no_member') ?: 'BS';
        $noMember  = $member ? $member->no_member : MemberModel::generateNoMember($prefix);
        $lokasiList = LokasiModel::query()->aktif()->orderBy('nama')->get();

        return view('banksampah::backend.member.form', [
            'member'      => $member,
            'noMember'    => $noMember,
            'lokasiList'  => $lokasiList,
            'form_action' => $id
                ? ci_route('bank-sampah.member.update', $id)
                : ci_route('bank-sampah.member.insert'),
        ]);
    }

    public function insert()
    {
        isCan('u');

        $req = MemberRequest::createFrom(request());
        $req->validate($req->rules());

        MemberModel::create([
            'no_member'   => request('no_member'),
            'nama'        => request('nama'),
            'no_hp'       => request('no_hp'),
            'alamat'      => request('alamat'),
            'penduduk_id' => request('penduduk_id'),
            'lokasi_id'   => request('lokasi_id'),
            'aktif'       => request('aktif', 1),
        ]);

        return redirect_with('success', 'Nasabah berhasil ditambahkan', ci_route('bank-sampah.member'));
    }

    public function update($id = null)
    {
        isCan('u');
        $member   = MemberModel::query()->findOrFail($id);

        $req = MemberRequest::createFrom(request());
        $req->validate($req->updateRules());

        $member->update([
            'nama'        => request('nama'),
            'no_hp'       => request('no_hp'),
            'alamat'      => request('alamat'),
            'penduduk_id' => request('penduduk_id'),
            'lokasi_id'   => request('lokasi_id'),
            'aktif'       => request('aktif', 1),
        ]);

        return redirect_with('success', 'Nasabah berhasil diubah', ci_route('bank-sampah.member'));
    }

    public function delete($id = null): void
    {
        isCan('d');
        $member = MemberModel::query()->findOrFail($id);

        if ($member->transaksi()->exists()) {
            redirect_with('error', 'Tidak bisa hapus, nasabah masih memiliki transaksi', ci_route('bank-sampah.member'));
            return;
        }

        $member->delete();

        redirect_with('success', 'Nasabah berhasil dihapus', ci_route('bank-sampah.member'));
    }

}
