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

/*
|--------------------------------------------------------------------------
| Web Routes - Bank Sampah Module (format Luthier CI3)
|--------------------------------------------------------------------------
| Aturan format (mengikuti modul resmi OpenSID):
| - TANPA `use Illuminate\Support\Facades\Route` — router modul adalah
|   Luthier CI (dimuat RouterHook inti), bukan router Laravel.
| - Group: Route::group('prefix', ['namespace' => 'BankSampah/BackEnd'], fn).
| - Controller sebagai string 'Controller@method' (KELAS GLOBAL, tanpa namespace).
| - Nama route pakai TITIK; route frontend warga berprefix `fweb.`.
| - {id} di belakang path (action/{id}) agar cocok dengan url()/route().
| - Semua aksi yang MENGUBAH data memakai POST.
|--------------------------------------------------------------------------
*/

// ==================== BACKEND (Admin) ====================
Route::group('bank-sampah', ['namespace' => 'BankSampah/BackEnd'], static function (): void {

    // Dashboard
    Route::get('/', 'DashboardController@index')->name('bank-sampah.index');
    Route::get('dashboard', 'DashboardController@index')->name('bank-sampah.dashboard');

    // Kategori Sampah
    Route::get('kategori-sampah', 'KategoriController@index')->name('bank-sampah.kategori-sampah.index');
    Route::post('kategori-sampah', 'KategoriController@index')->name('bank-sampah.kategori-sampah.datatables');
    Route::get('kategori-sampah/form/{id?}', 'KategoriController@form')->name('bank-sampah.kategori-sampah.form');
    Route::post('kategori-sampah/insert', 'KategoriController@insert')->name('bank-sampah.kategori-sampah.insert');
    Route::post('kategori-sampah/update/{id?}', 'KategoriController@update')->name('bank-sampah.kategori-sampah.update');
    Route::post('kategori-sampah/delete/{id?}', 'KategoriController@delete')->name('bank-sampah.kategori-sampah.delete');

    // Jenis Sampah
    Route::get('jenis-sampah', 'SampahController@index')->name('bank-sampah.jenis-sampah.index');
    Route::post('jenis-sampah', 'SampahController@index')->name('bank-sampah.jenis-sampah.datatables');
    Route::get('jenis-sampah/form/{id?}', 'SampahController@form')->name('bank-sampah.jenis-sampah.form');
    Route::post('jenis-sampah/insert', 'SampahController@insert')->name('bank-sampah.jenis-sampah.insert');
    Route::post('jenis-sampah/update/{id?}', 'SampahController@update')->name('bank-sampah.jenis-sampah.update');
    Route::post('jenis-sampah/delete/{id?}', 'SampahController@delete')->name('bank-sampah.jenis-sampah.delete');

    // Nasabah (Member)
    Route::get('member', 'MemberController@index')->name('bank-sampah.member.index');
    Route::post('member', 'MemberController@index')->name('bank-sampah.member.datatables');
    Route::get('member/form/{id?}', 'MemberController@form')->name('bank-sampah.member.form');
    Route::post('member/insert', 'MemberController@insert')->name('bank-sampah.member.insert');
    Route::post('member/update/{id?}', 'MemberController@update')->name('bank-sampah.member.update');
    Route::post('member/delete/{id?}', 'MemberController@delete')->name('bank-sampah.member.delete');
    Route::get('member/detail/{id}', 'MemberController@detail')->name('bank-sampah.member.detail');
    Route::get('member/kartu/{id}', 'MemberController@cetakKartu')->name('bank-sampah.member.kartu');

    // Aksi admin atas akun warga (D-001: pindah dari controller publik warga)
    Route::post('member/reset-uuid/{id}', 'MemberController@resetUuid')->name('bank-sampah.member.reset-uuid');
    Route::post('member/set-pin/{id}', 'MemberController@setPin')->name('bank-sampah.member.set-pin');
    Route::post('member/share-link/{id}', 'MemberController@shareLink')->name('bank-sampah.member.share-link');

    // Transaksi Setor / Tarik
    Route::get('transaksi', 'TransaksiController@index')->name('bank-sampah.transaksi.index');
    Route::post('transaksi', 'TransaksiController@index')->name('bank-sampah.transaksi.datatables');
    Route::get('transaksi/setor', 'TransaksiController@formSetor')->name('bank-sampah.transaksi.setor');
    Route::post('transaksi/proses-setor', 'TransaksiController@prosesSetor')->name('bank-sampah.transaksi.proses-setor');
    Route::get('transaksi/tarik', 'TransaksiController@formTarik')->name('bank-sampah.transaksi.tarik');
    Route::post('transaksi/proses-tarik', 'TransaksiController@prosesTarik')->name('bank-sampah.transaksi.proses-tarik');
    Route::get('transaksi/detail/{id}', 'TransaksiController@detail')->name('bank-sampah.transaksi.detail');
    Route::get('transaksi/struk/{id}', 'TransaksiController@cetakStruk')->name('bank-sampah.transaksi.struk');

    // AJAX endpoints
    Route::get('ajax/member', 'TransaksiController@getMember')->name('bank-sampah.ajax.member');
    Route::get('ajax/member-cari', 'TransaksiController@cariMember')->name('bank-sampah.ajax.member-cari');
    Route::get('ajax/sampah', 'TransaksiController@getSampah')->name('bank-sampah.ajax.sampah');
    Route::get('ajax/penduduk', 'MemberController@ajaxPenduduk')->name('bank-sampah.ajax.penduduk');
    Route::get('ajax/penduduk/{id}', 'MemberController@ajaxPendudukDetail')->name('bank-sampah.ajax.penduduk.detail');

    // Lokasi (TPS)
    Route::get('lokasi', 'LokasiController@index')->name('bank-sampah.lokasi.index');
    Route::post('lokasi', 'LokasiController@index')->name('bank-sampah.lokasi.datatables');
    Route::get('lokasi/form/{id?}', 'LokasiController@form')->name('bank-sampah.lokasi.form');
    Route::post('lokasi/insert', 'LokasiController@insert')->name('bank-sampah.lokasi.insert');
    Route::post('lokasi/update/{id?}', 'LokasiController@update')->name('bank-sampah.lokasi.update');
    Route::post('lokasi/delete/{id?}', 'LokasiController@delete')->name('bank-sampah.lokasi.delete');

    // Reward & Poin
    Route::get('reward', 'RewardController@index')->name('bank-sampah.reward.index');
    Route::get('reward/tukar/{memberId}', 'RewardController@formTukar')->name('bank-sampah.reward.tukar');
    Route::post('reward/proses-tukar/{memberId}', 'RewardController@prosesTukar')->name('bank-sampah.reward.proses-tukar');
    Route::get('reward/riwayat/{memberId}', 'RewardController@riwayat')->name('bank-sampah.reward.riwayat');

    // Laporan (export = baca data, tetap GET)
    Route::get('laporan', 'LaporanController@index')->name('bank-sampah.laporan.index');
    Route::get('laporan/export-pdf', 'LaporanController@exportPdf')->name('bank-sampah.laporan.export-pdf');
    Route::get('laporan/export-csv', 'LaporanController@exportCsv')->name('bank-sampah.laporan.export-csv');
});

// ==================== FRONTEND (Dashboard Warga - Publik) ====================
// D-001: autentikasi warga lewat Layanan Mandiri inti (NIK + PIN).
// Tidak ada route login/verifikasi PIN buatan modul. Logout = POST,
// hanya forget key session milik modul (bukan invalidate() sesi inti).
Route::group('bank-sampah/warga', ['namespace' => 'BankSampah/FrontEnd'], static function (): void {
    Route::get('/', 'WargaController@index')->name('fweb.bank-sampah.warga.index');
    Route::post('logout', 'WargaController@logout')->name('fweb.bank-sampah.warga.logout');
    Route::get('share/{token}', 'WargaController@shareAccess')->name('fweb.bank-sampah.warga.share');
});
