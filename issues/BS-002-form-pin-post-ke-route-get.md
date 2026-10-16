# Issue: Form PIN warga POST ke route GET-only — verifikasi PIN tidak pernah bisa jalan

| Field | Value |
|-------|-------|
| ID | BS-002 |
| Severity | CRITICAL |
| Kategori | Bug / Security |
| Status | Open |
| File | `Views/backend/warga/index.blade.php:88` |

## Deskripsi
Form verifikasi PIN di dashboard warga mengirim POST ke `url('bank-sampah/warga')`, tapi route tersebut hanya didefinisikan sebagai GET (`Routes/web.php:124`). Tidak ada route POST yang menangani submit PIN.

## Dampak
POST ke route GET-only → 405 Method Not Allowed. Logika pembacaan PIN di `BankSampahWargaController::index()` (baris 83) tidak pernah menerima submit. `require_pin` efektif mati — nasabah masuk dashboard tanpa faktor kedua.

## Reproduksi
1. Login warga (no_member + no_hp)
2. Muncul form "Verifikasi PIN"
3. Isi PIN, submit
4. Hasil: 405 Method Not Allowed (harusnya: verifikasi jalan)

## Bukti
Blade baris 88: `<form action="{{ url('bank-sampah/warga') }}" method="POST" class="pin-form">`

Routes/web.php:124: `Route::get('/', [BankSampahWargaController::class, 'index'])->name('index');` — tidak ada `Route::post` untuk prefix warga kecuali `login`.

## Perbaikan
Tambahkan route POST di group warga:
```php
Route::post('/', [BankSampahWargaController::class, 'index'])->name('verify');
```
atau pisahkan ke `Route::post('verify-pin', ...)` dengan handler khusus.

## Estimasi
30 menit
