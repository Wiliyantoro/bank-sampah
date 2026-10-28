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

use Illuminate\Support\Facades\DB;
use Modules\BankSampah\Services\SecurityManager;
use Modules\BankSampah\Models\MemberModel;

class MemberAjaxController extends AdminModulController
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

    // ==================== AJAX ====================
    // Dipisah dari MemberController (D-004: anti-bengkak, issue #92).

    // ==================== AJAX ====================

    /**
     * AJAX: Cari data penduduk untuk autocomplete
     */
    public function ajaxPenduduk()
    {
        $limit = min((int) request('limit', 20), 50);

        $query = DB::table('tweb_penduduk')
            ->select('id', 'nama', 'nik', 'alamat_sekarang', 'telepon', 'sex')
            // BS-036: penduduk hanya milik desa aktif (kolom config_id ada,
            // dipakai validasi BS-015) — tanpa ini autocomplete bocor PII
            // penduduk desa lain pada instalasi multi-desa
            ->where('config_id', identitas('id'))
            ->where('status_dasar', 1)
            ->orderBy('nama');

        // issue #66: when() menggantikan if manual
        $query->when(request()->filled('q'), function ($q) {
            $safeCari = bank_sampah_safe_like(trim((string) request('q')));
            $q->where(function ($w) use ($safeCari) {
                $w->where('nama', 'LIKE', "%{$safeCari}%")
                  ->orWhere('nik', 'LIKE', "%{$safeCari}%");
            });
        });

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
        $p = DB::table('tweb_penduduk')
            ->select('id', 'nama', 'nik', 'alamat_sekarang', 'telepon', 'sex')
            // BS-036: id wajib penduduk desa aktif
            ->where('config_id', identitas('id'))
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
