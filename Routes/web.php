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

use Illuminate\Support\Facades\Route;
use Modules\BankSampah\Http\Controllers\BackEnd\BankSampahDashboardController;
use Modules\BankSampah\Http\Controllers\BackEnd\BankSampahKategoriController;
use Modules\BankSampah\Http\Controllers\BackEnd\BankSampahSampahController;
use Modules\BankSampah\Http\Controllers\BackEnd\BankSampahMemberController;
use Modules\BankSampah\Http\Controllers\BackEnd\BankSampahTransaksiController;
use Modules\BankSampah\Http\Controllers\BackEnd\BankSampahLokasiController;
use Modules\BankSampah\Http\Controllers\BackEnd\BankSampahRewardController;
use Modules\BankSampah\Http\Controllers\BackEnd\BankSampahLaporanController;
use Modules\BankSampah\Http\Controllers\BackEnd\BankSampahWargaController;
use Modules\BankSampah\Http\Controllers\BackEnd\BankSampahPengaturanController;

/*
|--------------------------------------------------------------------------
| Web Routes - Bank Sampah Module
|--------------------------------------------------------------------------
| Route mengikuti konvensi OpenSID:
| - Prefix: bank-sampah
| - Auth middleware otomatis dari CI3
| - Route name pakai slash: bank-sampah/nama_rute
| - {id} HARUS di belakang (action/{id}) supaya match url()/route()
|   url('bank-sampah/member/detail', 1) → bank-sampah/member/detail/1
|
| PENTING: JANGAN definisikan ulang helper global ci_route() OpenSID di modul ini.
| Helper modul dimuat lebih awal dari helper inti sehingga menimpa ci_route() dan
| memutus URL dot-notation inti (mis. surat_master.datatables) -> 404 di semua
| halaman DataTables OpenSID. Selalu pakai url()/route() Laravel di view modul.
|--------------------------------------------------------------------------
*/

// ==================== Admin Routes ====================
Route::group(['prefix' => 'bank-sampah', 'as' => 'bank-sampah/', 'middleware' => ['web', 'banksampah.auth']], function () {

    // Dashboard
    Route::get('/', [BankSampahDashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard', [BankSampahDashboardController::class, 'index'])->name('dashboard2');

    // ==================== Kategori ====================
    Route::get('kategori-sampah', [BankSampahKategoriController::class, 'index'])->name('kategori');
    Route::get('kategori-sampah/tambah', [BankSampahKategoriController::class, 'create'])->name('kategori/tambah');
    Route::post('kategori-sampah/simpan', [BankSampahKategoriController::class, 'store'])->name('kategori/simpan');
    Route::get('kategori-sampah/ubah/{id}', [BankSampahKategoriController::class, 'edit'])->name('kategori/ubah');
    Route::post('kategori-sampah/update/{id}', [BankSampahKategoriController::class, 'update'])->name('kategori/update');
    Route::post('kategori-sampah/hapus/{id}', [BankSampahKategoriController::class, 'destroy'])->name('kategori/hapus');

    // ==================== Jenis Sampah ====================
    Route::get('jenis-sampah', [BankSampahSampahController::class, 'index'])->name('sampah');
    Route::get('jenis-sampah/tambah', [BankSampahSampahController::class, 'create'])->name('sampah/tambah');
    Route::post('jenis-sampah/simpan', [BankSampahSampahController::class, 'store'])->name('sampah/simpan');
    Route::get('jenis-sampah/ubah/{id}', [BankSampahSampahController::class, 'edit'])->name('sampah/ubah');
    Route::post('jenis-sampah/update/{id}', [BankSampahSampahController::class, 'update'])->name('sampah/update');
    Route::post('jenis-sampah/hapus/{id}', [BankSampahSampahController::class, 'destroy'])->name('sampah/hapus');

    // ==================== Nasabah/Member ====================
    Route::get('member', [BankSampahMemberController::class, 'index'])->name('member');
    Route::get('member/tambah', [BankSampahMemberController::class, 'create'])->name('member/tambah');
    Route::post('member/simpan', [BankSampahMemberController::class, 'store'])->name('member/simpan');
    Route::get('member/ubah/{id}', [BankSampahMemberController::class, 'edit'])->name('member/ubah');
    Route::post('member/update/{id}', [BankSampahMemberController::class, 'update'])->name('member/update');
    Route::post('member/hapus/{id}', [BankSampahMemberController::class, 'destroy'])->name('member/hapus');
    Route::get('member/detail/{id}', [BankSampahMemberController::class, 'detail'])->name('member/detail');
    Route::get('member/kartu/{id}', [BankSampahMemberController::class, 'cetakKartu'])->name('member/kartu');

    // ==================== Transaksi ====================
    Route::get('transaksi', [BankSampahTransaksiController::class, 'index'])->name('transaksi');
    Route::get('transaksi/setor', [BankSampahTransaksiController::class, 'formSetor'])->name('transaksi/setor');
    Route::post('transaksi/proses-setor', [BankSampahTransaksiController::class, 'prosesSetor'])->name('transaksi/proses-setor');
    Route::get('transaksi/tarik', [BankSampahTransaksiController::class, 'formTarik'])->name('transaksi/tarik');
    Route::post('transaksi/proses-tarik', [BankSampahTransaksiController::class, 'prosesTarik'])->name('transaksi/proses-tarik');
    Route::get('transaksi/detail/{id}', [BankSampahTransaksiController::class, 'detail'])->name('transaksi/detail');
    Route::get('transaksi/struk/{id}', [BankSampahTransaksiController::class, 'cetakStruk'])->name('transaksi/struk');

    // AJAX endpoints
    Route::get('ajax/member', [BankSampahTransaksiController::class, 'getMember'])->name('ajax/member');
    Route::get('ajax/sampah', [BankSampahTransaksiController::class, 'getSampah'])->name('ajax/sampah');
    Route::get('ajax/penduduk', [BankSampahMemberController::class, 'ajaxPenduduk'])->name('ajax/penduduk');
    Route::get('ajax/penduduk/{id}', [BankSampahMemberController::class, 'ajaxPendudukDetail'])->name('ajax/penduduk/detail');

    // ==================== Lokasi/TPS ====================
    Route::get('lokasi', [BankSampahLokasiController::class, 'index'])->name('lokasi');
    Route::get('lokasi/tambah', [BankSampahLokasiController::class, 'create'])->name('lokasi/tambah');
    Route::post('lokasi/simpan', [BankSampahLokasiController::class, 'store'])->name('lokasi/simpan');
    Route::get('lokasi/ubah/{id}', [BankSampahLokasiController::class, 'edit'])->name('lokasi/ubah');
    Route::post('lokasi/update/{id}', [BankSampahLokasiController::class, 'update'])->name('lokasi/update');
    Route::post('lokasi/hapus/{id}', [BankSampahLokasiController::class, 'destroy'])->name('lokasi/hapus');

    // ==================== Reward & Poin ====================
    Route::get('reward', [BankSampahRewardController::class, 'index'])->name('reward');
    Route::get('reward/tukar/{memberId}', [BankSampahRewardController::class, 'formTukar'])->name('reward/tukar');
    Route::post('reward/proses-tukar/{memberId}', [BankSampahRewardController::class, 'prosesTukar'])->name('reward/proses-tukar');
    Route::get('reward/riwayat/{memberId}', [BankSampahRewardController::class, 'riwayat'])->name('reward/riwayat');

    // ==================== Laporan ====================
    Route::get('laporan', [BankSampahLaporanController::class, 'index'])->name('laporan');
    Route::get('laporan/export-pdf', [BankSampahLaporanController::class, 'exportPdf'])->name('laporan/export-pdf');
    Route::get('laporan/export-csv', [BankSampahLaporanController::class, 'exportCsv'])->name('laporan/export-csv');

    // ==================== Pengaturan ====================
    Route::get('pengaturan', [BankSampahPengaturanController::class, 'index'])->name('pengaturan');
    Route::post('pengaturan/update-warga', [BankSampahPengaturanController::class, 'updateWarga'])->name('pengaturan/update-warga');

    // ==================== Member Management (UUID + PIN) ====================
    Route::post('member/reset-uuid/{id}', [BankSampahWargaController::class, 'resetUuid'])->name('member/reset-uuid');
    Route::post('member/set-pin/{id}', [BankSampahWargaController::class, 'setPin'])->name('member/set-pin');
    Route::get('member/share-link/{id}', [BankSampahWargaController::class, 'shareLink'])->name('member/share-link');
});

// ==================== Dashboard Warga (Public - Session Based) ====================
// Akses via session: login → session stores member_id → no UUID in URL
Route::group(['prefix' => 'bank-sampah/warga', 'as' => 'bank-sampah/warga/', 'middleware' => ['web']], function () {
    Route::get('/', [BankSampahWargaController::class, 'index'])->name('index');
    Route::post('login', [BankSampahWargaController::class, 'login'])->name('login');
    Route::get('logout', [BankSampahWargaController::class, 'logout'])->name('logout');
    Route::get('share/{token}', [BankSampahWargaController::class, 'shareAccess'])->name('share');
});
