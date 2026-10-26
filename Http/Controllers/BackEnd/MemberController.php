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
use Modules\BankSampah\Libraries\SecurityManager;
use Modules\BankSampah\Models\LokasiModel;
use Modules\BankSampah\Models\MemberModel;
use Modules\BankSampah\Models\TransaksiModel;

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
        $configId = identitas('id');

        request()->validate([
            'no_member'   => "required|string|max:20|unique:bank_sampah_member,no_member,NULL,id,config_id,{$configId}",
            'nama'        => 'required|string|max:150',
            'no_hp'       => 'nullable|string|max:20',
            'alamat'      => 'nullable|string',
            // BS-015: ID eksternal wajib ter-scope desa agar tidak bisa
            // dilink ke data penduduk/lokasi desa lain
            'penduduk_id' => "nullable|integer|exists:tweb_penduduk,id,config_id,{$configId}",
            'lokasi_id'   => "nullable|integer|exists:bank_sampah_lokasi,id,config_id,{$configId}",
        ]);

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
        $configId = identitas('id');
        $member   = MemberModel::query()->findOrFail($id);

        request()->validate([
            'nama'        => 'required|string|max:150',
            'no_hp'       => 'nullable|string|max:20',
            'alamat'      => 'nullable|string',
            // BS-015: ID eksternal wajib ter-scope desa
            'penduduk_id' => 'nullable|integer|exists:tweb_penduduk,id,config_id,' . $configId,
            'lokasi_id'   => 'nullable|integer|exists:bank_sampah_lokasi,id,config_id,' . $configId,
        ]);

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
        $member = MemberModel::query()->findOrFail($id);

        if ($member->transaksi()->exists()) {
            redirect_with('error', 'Tidak bisa hapus, nasabah masih memiliki transaksi', ci_route('bank-sampah.member'));
            return;
        }

        $member->delete();

        redirect_with('success', 'Nasabah berhasil dihapus', ci_route('bank-sampah.member'));
    }

    /**
     * Detail nasabah + riwayat transaksi
     */
    public function detail($id)
    {
        // issue #63: eager load transaksi.detail.sampah tanpa batas dihapus —
        // riwayat sudah disajikan via $riwayatTransaksi yang di-paginate.
        $member = MemberModel::desa()->with('lokasi')
            ->findOrFail($id);

        $riwayatTransaksi = TransaksiModel::query()
            ->where('member_id', $id)
            ->with('detail.sampah')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('banksampah::backend.member.detail', compact('member', 'riwayatTransaksi'));
    }

    /**
     * Cetak kartu nasabah (QR Code)
     */
    public function cetakKartu($id)
    {
        $member = MemberModel::query()->with('lokasi')->findOrFail($id);

        // Identitas bank sampah dari Pengaturan Aplikasi (item 5)
        $config = $this->identitasBankSampah();

        // BS-023: QR memuat URL dashboard (halaman login) — bukan data member.
        // no_member = separuh kredensial login warga; tidak boleh dikodekan di QR.
        $shareUrl = url('bank-sampah/warga');

        return view('banksampah::backend.member.kartu', compact('member', 'config', 'shareUrl'));
    }

    // ==================== Aksi admin atas akun warga (D-001) ====================

    /**
     * Reset UUID — link share lama tidak berlaku lagi
     */
    public function resetUuid($id)
    {
        isCan('u');

        $member = MemberModel::query()->findOrFail($id);
        $oldUuid = $member->uuid;
        $member->resetUuid();

        $security = app(SecurityManager::class);
        $security->logEvent('uuid_reset', [
            'member_id' => $id,
            'old_uuid'  => substr($oldUuid, 0, 8) . '...',
            'admin_id'  => auth()->id(),
        ]);

        return redirect_with('success', 'UUID berhasil di-reset. Link lama tidak berlaku lagi.', ci_route('bank-sampah.member'));
    }

    /**
     * Set PIN Layanan Mandiri warga (D-001: PIN milik sesi inti, disimpan
     * pada tweb_penduduk.pin — bukan kolom pin tabel member).
     */
    public function setPin($id)
    {
        isCan('u');

        request()->validate([
            'pin' => ['required', 'string', 'regex:/^[0-9]{4,6}$/'],
        ]);

        $member = MemberModel::query()->findOrFail($id);

        if (empty($member->penduduk_id)) {
            return redirect_with('error', 'Nasabah belum terhubung dengan data penduduk. Hubungkan dulu untuk mengatur PIN Layanan Mandiri.', ci_route('bank-sampah.member'));
        }

        $pin = request('pin');
        $security = app(SecurityManager::class);

        if ($security->isCommonPin($pin)) {
            return redirect_with('error', 'PIN terlalu umum. Gunakan kombinasi angka yang lebih unik.', ci_route('bank-sampah.member'));
        }

        DB::table('tweb_penduduk')
            // BS-015/BS-036: hanya penduduk desa aktif
            ->where('config_id', identitas('id'))
            ->where('id', $member->penduduk_id)
            ->update(['pin' => $pin]);

        $security->logEvent('pin_changed', [
            'member_id' => $id,
            'admin_id'  => auth()->id(),
        ]);

        return redirect_with('success', 'PIN berhasil diatur.', ci_route('bank-sampah.member'));
    }

    /**
     * Generate link share untuk nasabah
     */
    public function shareLink($id)
    {
        isCan('u');

        $member = MemberModel::query()->findOrFail($id);

        if (empty($member->uuid)) {
            $member->uuid = app(SecurityManager::class)->generateToken();
            $member->save();
        }

        $security  = app(SecurityManager::class);
        $shareToken = $security->createExpiringToken($member->id, 60);
        $shareUrl   = url('bank-sampah/warga/share/' . $shareToken);

        $security->logEvent('share_link_generated', ['member_id' => $id]);

        return response()->json([
            'success' => true,
            'data'    => [
                'uuid'       => $member->uuid,
                'share_url'  => $shareUrl,
                'expires_in' => '1 jam',
            ],
        ]);
    }

    // ==================== AJAX ====================

    /**
     * AJAX: Cari data penduduk untuk autocomplete
     */
    public function ajaxPenduduk()
    {
        $cari  = request('q', '');
        $limit = min((int) request('limit', 20), 50);

        $query = DB::table('tweb_penduduk')
            ->select('id', 'nama', 'nik', 'alamat_sekarang', 'telepon', 'sex')
            // BS-036: penduduk hanya milik desa aktif (kolom config_id ada,
            // dipakai validasi BS-015) — tanpa ini autocomplete bocor PII
            // penduduk desa lain pada instalasi multi-desa
            ->where('config_id', identitas('id'))
            ->where('status_dasar', 1)
            ->orderBy('nama');

        if ($cari !== '') {
            $safeCari = bank_sampah_safe_like($cari);
            $query->where(function ($q) use ($safeCari) {
                $q->where('nama', 'LIKE', "%{$safeCari}%")
                  ->orWhere('nik', 'LIKE', "%{$safeCari}%");
            });
        }

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

    /**
     * Identitas bank sampah untuk cetakan/kartu (Pengaturan Aplikasi, item 5):
     * nilai kosong otomatis memakai profil desa.
     */
    protected function identitasBankSampah(): array
    {
        return [
            'nama'   => setting('bank_sampah_nama') ?: (identitas('nama_desa') ?: 'Bank Sampah Desa'),
            'alamat' => setting('bank_sampah_alamat') ?: (identitas('alamat_kantor') ?: ''),
            'no_hp'  => setting('bank_sampah_no_hp') ?: (identitas('telepon') ?: ''),
            'logo'   => setting('bank_sampah_logo') ?: '',
        ];
    }
}
