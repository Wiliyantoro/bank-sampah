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
use Modules\BankSampah\Http\Controllers\API\BankSampahApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('bank-sampah')->group(function () {
    // Auth
    Route::post('login', [BankSampahApiController::class, 'login']);

    // Public endpoints
    Route::get('sampah', [BankSampahApiController::class, 'daftarSampah']);
    Route::get('kategori', [BankSampahApiController::class, 'daftarKategori']);

    // Member endpoints (butuh auth di production)
    Route::get('member/{id}/saldo', [BankSampahApiController::class, 'saldo']);
    Route::get('member/{id}/transaksi', [BankSampahApiController::class, 'riwayatTransaksi']);
});
